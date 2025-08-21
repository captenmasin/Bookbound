<?php

use App\Models\Book;
use App\Models\User;
use App\Enums\ActivityType;
use App\Enums\UserBookStatus;
use App\Actions\Books\UpdateUserBookStatus;
use App\Http\Requests\Books\UpdateBookUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

uses(RefreshDatabase::class);

describe('UpdateUserBookStatus', function () {
    it('successfully updates book status in user library', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::PlanToRead]);

        $action = app(UpdateUserBookStatus::class);
        $result = $action->handle($user, $book, UserBookStatus::Reading);

        expect($result)->toBe(true);

        $pivot = $user->books()->where('book_id', $book->id)->first()->pivot;
        expect($pivot->status)->toBe(UserBookStatus::Reading->value);
    });

    it('throws ModelNotFoundException when book not found in user library', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $action = app(UpdateUserBookStatus::class);

        expect(fn () => $action->handle($user, $book, UserBookStatus::Reading))
            ->toThrow(ModelNotFoundException::class, 'Book not found in your library.');
    });

    it('works with all UserBookStatus enum values', function ($status) {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::PlanToRead]);

        $action = app(UpdateUserBookStatus::class);
        $result = $action->handle($user, $book, $status);

        expect($result)->toBe(true);

        $pivot = $user->books()->where('book_id', $book->id)->first()->pivot;
        expect($pivot->status)->toBe($status->value);
    })->with([
        UserBookStatus::PlanToRead,
        UserBookStatus::Reading,
        UserBookStatus::Read,
        UserBookStatus::OnHold,
        UserBookStatus::Dropped,
    ]);

    it('logs user activity', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::PlanToRead]);

        $action = app(UpdateUserBookStatus::class);
        $action->handle($user, $book, UserBookStatus::Reading);

        $activity = $user->activities()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->type)->toBe(ActivityType::BookStatusUpdated->value)
            ->and($activity->properties['book_identifier'])->toBe($book->identifier)
            ->and($activity->properties['book_title'])->toBe($book->title)
            ->and($activity->properties['status'])->toBe(UserBookStatus::Reading->value);
    });

    test('asController() returns JSON success response when request wants JSON', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::PlanToRead]);

        $request = UpdateBookUserRequest::create('/api/user/books/'.$book->id, 'PATCH', [
            'status' => 'Reading',
        ]);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));
        $request->headers->set('Accept', 'application/json');
        $request->merge(['book' => $book]);

        $action = app(UpdateUserBookStatus::class);
        $response = $action->asController($request, $book);

        expect($response->getStatusCode())->toBe(200);
        $data = json_decode($response->getContent(), true);
        expect($data['success'])->toBeTrue()
            ->and($data['message'])->toBe('Book status updated successfully.')
            ->and($data['status'])->toBe(UserBookStatus::Reading->value);
    });

    test('asController() returns JSON error response when book not found in user library and request wants JSON', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $request = UpdateBookUserRequest::create('/api/user/books/'.$book->id, 'PATCH', [
            'status' => 'Reading',
        ]);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));
        $request->headers->set('Accept', 'application/json');
        $request->merge(['book' => $book]);

        $action = app(UpdateUserBookStatus::class);
        $response = $action->asController($request, $book);

        expect($response->getStatusCode())->toBe(404);
        $data = json_decode($response->getContent(), true);
        expect($data['success'])->toBeFalse()
            ->and($data['message'])->toBe('Book not found in your library.');
    });

    test('asController() returns redirect success response when request does not want JSON', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::PlanToRead]);

        $request = UpdateBookUserRequest::create('/user/books/'.$book->id, 'PATCH', [
            'status' => 'Reading',
        ]);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));
        $request->merge(['book' => $book]);

        $action = app(UpdateUserBookStatus::class);
        $response = $action->asController($request, $book);

        expect($response->getStatusCode())->toBe(302)
            ->and($response)
            ->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
    });

    test('asController() returns redirect error response when book not found in user library and request does not want JSON', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $request = UpdateBookUserRequest::create('/user/books/'.$book->id, 'PATCH', [
            'status' => 'Reading',
        ]);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));
        $request->merge(['book' => $book]);

        $action = app(UpdateUserBookStatus::class);
        $response = $action->asController($request, $book);

        expect($response->getStatusCode())->toBe(302)
            ->and($response)
            ->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class)
            ->and($response->getSession()->get('error'))->toBe('Book not found in your library.');
    });

    test('asController() uses default status when status not provided', function () {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => UserBookStatus::Reading]);

        $request = UpdateBookUserRequest::create('/api/user/books/'.$book->id, 'PATCH', []);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));
        $request->headers->set('Accept', 'application/json');
        $request->merge(['book' => $book]);

        $action = app(UpdateUserBookStatus::class);
        $response = $action->asController($request, $book);

        expect($response->getStatusCode())->toBe(200);
        $data = json_decode($response->getContent(), true);
        expect($data['success'])->toBeTrue();

        $pivot = $user->books()->where('book_id', $book->id)->first()->pivot;
        expect($pivot->status)->toBe(UserBookStatus::PlanToRead->value);
    });

    it('can update from any status to any other status', function ($fromStatus, $toStatus) {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->books()->attach($book, ['status' => $fromStatus]);

        $action = app(UpdateUserBookStatus::class);
        $result = $action->handle($user, $book, $toStatus);

        expect($result)->toBe(true);

        $pivot = $user->books()->where('book_id', $book->id)->first()->pivot;
        expect($pivot->status)->toBe($toStatus->value);
    })->with([
        [UserBookStatus::PlanToRead, UserBookStatus::Reading],
        [UserBookStatus::Reading, UserBookStatus::Read],
        [UserBookStatus::Reading, UserBookStatus::OnHold],
        [UserBookStatus::OnHold, UserBookStatus::Reading],
        [UserBookStatus::OnHold, UserBookStatus::Dropped],
        [UserBookStatus::Dropped, UserBookStatus::PlanToRead],
        [UserBookStatus::Read, UserBookStatus::PlanToRead],
    ]);
});
