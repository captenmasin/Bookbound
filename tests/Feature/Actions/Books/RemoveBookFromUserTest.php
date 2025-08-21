<?php

use App\Models\Book;
use App\Models\User;
use App\Models\Cover;
use App\Enums\ActivityType;
use App\Enums\UserBookStatus;
use App\Actions\Books\RemoveBookFromUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Requests\Books\DestroyBookUserRequest;

uses(RefreshDatabase::class);

describe('RemoveBookFromUser', function () {
    test('handle() removes book from user library', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);
        $action = app(RemoveBookFromUser::class);

        expect($user->books()->where('book_id', $book->id)->exists())->toBeTrue();

        $action->handle($book, $user);

        expect($user->books()->where('book_id', $book->id)->exists())->toBeFalse();
    });

    test('handle() throws exception when book is not in user library', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $action = app(RemoveBookFromUser::class);

        expect(fn () => $action->handle($book, $user))
            ->toThrow(Exception::class, 'Book not found in user library.');
    });

    test('handle() removes associated book covers', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);

        // Create a custom cover for this book
        $cover = Cover::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $action = app(RemoveBookFromUser::class);

        expect(Cover::where('book_id', $book->id)->where('user_id', $user->id)->exists())->toBeTrue();

        $action->handle($book, $user);

        expect(Cover::where('book_id', $book->id)->where('user_id', $user->id)->exists())->toBeFalse();
    });

    test('handle() logs user activity', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);
        $action = app(RemoveBookFromUser::class);

        $action->handle($book, $user);

        $activity = $user->activities()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->type)->toBe(ActivityType::BookRemoved->value)
            ->and($activity->properties['book_identifier'])->toBe($book->identifier)
            ->and($activity->properties['book_title'])->toBe($book->title);
    });

    test('handle() detaches book from user relationship', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);
        $action = app(RemoveBookFromUser::class);

        expect($user->books()->count())->toBe(1);

        $action->handle($book, $user);

        expect($user->books()->count())->toBe(0);
    });

    test('handle() works with books that have no covers', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);
        $action = app(RemoveBookFromUser::class);

        // Should not throw an exception even if there are no covers
        $action->handle($book, $user);

        expect($user->books()->where('book_id', $book->id)->exists())->toBeFalse();
    });

    test('asController() returns JSON success response when request wants JSON', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);

        $request = DestroyBookUserRequest::create('/api/user/books/'.$book->id, 'DELETE');
        $request->setUserResolver(fn () => $user);
        $request->headers->set('Accept', 'application/json');

        $action = app(RemoveBookFromUser::class);
        $response = $action->asController($request, $book);

        expect($response->getStatusCode())->toBe(200);
        $data = json_decode($response->getContent(), true);
        expect($data['success'])->toBeTrue()
            ->and($data['message'])->toBe('Book removed from your library successfully.');
    });

    test('asController() returns JSON error response when book not in library and request wants JSON', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $request = DestroyBookUserRequest::create('/api/user/books/'.$book->id, 'DELETE');
        $request->setUserResolver(fn () => $user);
        $request->headers->set('Accept', 'application/json');

        $action = app(RemoveBookFromUser::class);
        $response = $action->asController($request, $book);

        expect($response->getStatusCode())->toBe(400);
        $data = json_decode($response->getContent(), true);
        expect($data['success'])->toBeFalse()
            ->and($data['message'])->toBe('Book not found in user library.');
    });

    test('asController() returns redirect success response when request does not want JSON', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);

        $request = DestroyBookUserRequest::create('/user/books/'.$book->id, 'DELETE');
        $request->setUserResolver(fn () => $user);

        $action = app(RemoveBookFromUser::class);
        $response = $action->asController($request, $book);

        expect($response->getStatusCode())->toBe(302)
            ->and($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
    });

    test('asController() returns redirect error response when request does not want JSON and error occurs', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $request = DestroyBookUserRequest::create('/user/books/'.$book->id, 'DELETE');
        $request->setUserResolver(fn () => $user);

        $action = app(RemoveBookFromUser::class);
        $response = $action->asController($request, $book);

        expect($response->getStatusCode())->toBe(302)
            ->and($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
    });

    test('handle() removes multiple covers if user has multiple for same book', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);

        // Create multiple covers for the same book
        $cover1 = Cover::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $cover2 = Cover::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $action = app(RemoveBookFromUser::class);

        expect(Cover::where('book_id', $book->id)->where('user_id', $user->id)->count())->toBe(2);

        $action->handle($book, $user);

        expect(Cover::where('book_id', $book->id)->where('user_id', $user->id)->count())->toBe(0);
    });

    test('handle() only removes covers belonging to the user for this book', function () {
        $book = Book::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->books()->attach($book, ['status' => UserBookStatus::Reading]);
        $user2->books()->attach($book, ['status' => UserBookStatus::Reading]);

        // Create covers for both users
        $cover1 = Cover::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
        ]);

        $cover2 = Cover::factory()->create([
            'user_id' => $user2->id,
            'book_id' => $book->id,
        ]);

        $action = app(RemoveBookFromUser::class);

        $action->handle($book, $user1);

        // User1's cover should be removed, user2's should remain
        expect(Cover::where('book_id', $book->id)->where('user_id', $user1->id)->exists())->toBeFalse()
            ->and(Cover::where('book_id', $book->id)->where('user_id', $user2->id)->exists())->toBeTrue();
    });
});
