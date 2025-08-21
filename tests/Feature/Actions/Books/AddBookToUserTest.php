<?php

use App\Models\Book;
use App\Models\User;
use App\Enums\ActivityType;
use App\Enums\UserBookStatus;
use App\Support\SubscriptionLimits;
use App\Actions\Books\AddBookToUser;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\Books\StoreBookUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('AddBookToUser', function () {
    test('handle() adds book to user library with default status', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $action = app(AddBookToUser::class);

        $action->handle($book, $user);

        expect($user->books()->where('book_id', $book->id)->exists())->toBeTrue();
        $pivot = $user->books()->where('book_id', $book->id)->first()->pivot;
        expect($pivot->status)->toBe(UserBookStatus::PlanToRead->value);
    });

    test('handle() adds book to user library with specified status', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $action = app(AddBookToUser::class);

        $action->handle($book, $user, UserBookStatus::Reading);

        expect($user->books()->where('book_id', $book->id)->exists())->toBeTrue();
        $pivot = $user->books()->where('book_id', $book->id)->first()->pivot;
        expect($pivot->status)->toBe(UserBookStatus::Reading->value);
    });

    test('handle() works with all UserBookStatus enum values', function ($status) {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $action = app(AddBookToUser::class);

        $action->handle($book, $user, $status);

        expect($user->books()->where('book_id', $book->id)->exists())->toBeTrue();
        $pivot = $user->books()->where('book_id', $book->id)->first()->pivot;
        expect($pivot->status)->toBe($status->value);
    })->with([
        UserBookStatus::PlanToRead,
        UserBookStatus::Reading,
        UserBookStatus::Read,
        UserBookStatus::OnHold,
        UserBookStatus::Dropped,
    ]);

    test('handle() throws exception when book already exists in user library', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);
        $action = app(AddBookToUser::class);

        expect(fn () => $action->handle($book, $user))
            ->toThrow(Exception::class, 'Book already exists in your library.');
    });

    test('handle() logs user activity', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $action = app(AddBookToUser::class);

        $action->handle($book, $user, UserBookStatus::Reading);

        $activity = $user->activities()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->type)->toBe(ActivityType::BookAdded->value)
            ->and($activity->properties['book_identifier'])->toBe($book->identifier)
            ->and($activity->properties['book_title'])->toBe($book->title)
            ->and($activity->properties['status'])->toBe(UserBookStatus::Reading->value);
    });

    test('handle() respects subscription limits', function () {
        Config::set('subscriptions.plans.free.limits.max_books', 1);

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $user = User::factory()->create();

        // Add first book (should succeed)
        $user->books()->attach($book1, ['status' => UserBookStatus::Reading]);

        $action = app(AddBookToUser::class);

        expect(fn () => $action->handle($book2, $user))
            ->toThrow(Exception::class);
    });

    test('handle() allows unlimited books when no limit is set', function () {
        Config::set('subscriptions.plans.free.limits', []); // No limits

        $books = Book::factory()->count(5)->create();
        $user = User::factory()->create();
        $action = app(AddBookToUser::class);

        // Add multiple books
        foreach ($books as $book) {
            $action->handle($book, $user);
        }

        expect($user->books()->count())->toBe(5);
    });

    test('asController() returns JSON success response when request wants JSON', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $request = StoreBookUserRequest::create('/api/user/books', 'POST', [
            'identifier' => $book->identifier,
            'status' => 'Reading',
        ]);
        $request->setUserResolver(fn () => $user);
        $request->headers->set('Accept', 'application/json');

        $action = app(AddBookToUser::class);
        $response = $action->asController($request);

        expect($response->getStatusCode())->toBe(200);
        $data = json_decode($response->getContent(), true);
        expect($data['success'])->toBeTrue()
            ->and($data['message'])->toBe('Book added to your library successfully.');
    });

    test('asController() returns JSON error response for duplicate book when request wants JSON', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);

        $request = StoreBookUserRequest::create('/api/user/books', 'POST', [
            'identifier' => $book->identifier,
            'status' => 'Reading',
        ]);
        $request->setUserResolver(fn () => $user);
        $request->headers->set('Accept', 'application/json');

        $action = app(AddBookToUser::class);
        $response = $action->asController($request);

        expect($response->getStatusCode())->toBe(400);
        $data = json_decode($response->getContent(), true);
        expect($data['success'])->toBeFalse()
            ->and($data['message'])->toBe('Book already exists in your library.');
    });

    test('asController() returns redirect success response when request does not want JSON', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $request = StoreBookUserRequest::create('/user/books', 'POST', [
            'identifier' => $book->identifier,
            'status' => 'Reading',
        ]);
        $request->setUserResolver(fn () => $user);

        $action = app(AddBookToUser::class);
        $response = $action->asController($request);

        expect($response->getStatusCode())->toBe(302)
            ->and($response)
            ->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
    });

    test('asController() returns redirect error response when request does not want JSON and error occurs', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);

        $request = StoreBookUserRequest::create('/user/books', 'POST', [
            'identifier' => $book->identifier,
            'status' => 'Reading',
        ]);
        $request->setUserResolver(fn () => $user);

        $action = app(AddBookToUser::class);
        $response = $action->asController($request);

        expect($response->getStatusCode())->toBe(302)
            ->and($response)
            ->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class)
            ->and($response->getSession()->get('error'))->toBe('Book already exists in your library.');
    });

    test('asController() returns error response when book is not found', function () {
        $user = User::factory()->create();

        $request = StoreBookUserRequest::create('/api/user/books', 'POST', [
            'identifier' => 'nonexistent-book',
            'status' => 'Reading',
        ]);
        $request->setUserResolver(fn () => $user);
        $request->headers->set('Accept', 'application/json');

        $action = app(AddBookToUser::class);
        $response = $action->asController($request);

        expect($response->getStatusCode())->toBe(400);
        $data = json_decode($response->getContent(), true);
        expect($data['success'])->toBeFalse()
            ->and($data['message'])->toBe('Book not found.');
    });

    test('asController() returns redirect error response when book is not found and request does not want JSON', function () {
        $user = User::factory()->create();

        $request = StoreBookUserRequest::create('/user/books', 'POST', [
            'identifier' => 'nonexistent-book',
            'status' => 'Reading',
        ]);
        $request->setUserResolver(fn () => $user);

        $action = app(AddBookToUser::class);
        $response = $action->asController($request);

        expect($response->getStatusCode())->toBe(302)
            ->and($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class)
            ->and($response->getSession()->get('error'))->toBe('Book not found.');
    });

    test('subscription limits work correctly with SubscriptionLimits class', function () {
        $user = User::factory()->create();

        expect(SubscriptionLimits::canAddBook($user))->toBeTrue(); // No limits by default

        Config::set('subscriptions.plans.free.limits.max_books', 0);
        expect(SubscriptionLimits::canAddBook($user))->toBeFalse(); // Zero limit

        Config::set('subscriptions.plans.free.limits.max_books', 1);
        expect(SubscriptionLimits::canAddBook($user))->toBeTrue(); // Under limit

        $book = Book::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);
        expect(SubscriptionLimits::canAddBook($user))->toBeFalse(); // At limit
    });
});
