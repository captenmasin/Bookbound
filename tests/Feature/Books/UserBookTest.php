<?php

use App\Models\Book;
use App\Models\User;
use App\Enums\UserBookStatus;
use Tests\Concerns\GiveSubscription;

uses(GiveSubscription::class);

test('book can be removed from library', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book, ['status' => UserBookStatus::PlanToRead->value]);

    $response = $this->actingAs($user)
        ->delete(route('api.user.books.destroy', $book));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('book_user', [
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);
});

test('book can be added to library', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('api.user.books.store'), [
            'identifier' => $book->identifier,
            'status' => UserBookStatus::Reading->value,
        ]);

    $response->assertSessionHas('success', 'Book added to your library successfully.');

    $this->assertDatabaseHas('book_user', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => UserBookStatus::Reading->value,
    ]);
});

test('book can be added to library as JSON', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('api.user.books.store'), [
            'identifier' => $book->identifier,
            'status' => UserBookStatus::Reading->value,
        ]);

    $response->assertJson(['success' => true, 'message' => 'Book added to your library successfully.']);

    $this->assertDatabaseHas('book_user', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => UserBookStatus::Reading->value,
    ]);
});

test('non-subscribed users cannot add more than defined limit books', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $limit = config('subscriptions.plans.free.limits.max_books');

    for ($i = 0; $i < $limit; $i++) {
        $user->books()->attach(Book::factory()->create(), ['status' => UserBookStatus::Reading->value]);
    }

    $book = Book::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('api.user.books.store'), [
            'identifier' => $book->identifier,
            'status' => UserBookStatus::Reading->value,
        ]);

    $response->assertRedirectBack();
    $response->assertSessionHas('error', 'You can have up to '.$limit.' books in your library. Remove a book or upgrade your plan to add more.');
    $this->assertDatabaseCount('book_user', $limit);
    $this->assertDatabaseMissing('book_user', [
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);

});

test('subscribed users can add unlimited books', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->giveActiveSubscription($user, config('subscriptions.plans.pro.key'));

    $limit = config('subscriptions.plans.free.limits.max_books');

    for ($i = 0; $i < $limit; $i++) {
        $user->books()->attach(Book::factory()->create(), ['status' => UserBookStatus::Reading->value]);
    }

    $book = Book::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('api.user.books.store'), [
            'identifier' => $book->identifier,
            'status' => UserBookStatus::Reading->value,
        ]);

    $response->assertRedirectBack();
    $this->assertDatabaseCount('book_user', $limit + 1);
    $this->assertDatabaseHas('book_user', [
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);

});

test('removing missing book returns an error', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $response = $this->actingAs($user)
        ->delete(route('api.user.books.destroy', $book));

    $response->assertForbidden();
});

test('book status can be updated', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book, ['status' => UserBookStatus::PlanToRead->value]);

    $response = $this->actingAs($user)
        ->patch(route('api.user.books.update_status', $book), [
            'status' => UserBookStatus::Completed->name,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('book_user', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => UserBookStatus::Completed->name,
    ]);
});

test('status update fails when book is missing', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $response = $this->actingAs($user)->patch(route('api.user.books.update_status', $book), [
        'status' => UserBookStatus::Completed->name,
    ]);

    $response->assertForbidden();
});

test('duplicate book cannot be added', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book, ['status' => UserBookStatus::PlanToRead->value]);

    $response = $this->actingAs($user)->post(route('api.user.books.store'), [
        'identifier' => $book->identifier,
        'status' => UserBookStatus::Reading->name,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $this->assertDatabaseCount('book_user', 1);
});

test('API returns error json on exception', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book);

    $response = $this->actingAs($user)
        ->postJson(route('api.user.books.store'), [
            'identifier' => $book->identifier,
            'status' => UserBookStatus::Reading->value,
        ]);

    $response->assertJson(['success' => false, 'message' => 'Book already exists in your library.']);
});
