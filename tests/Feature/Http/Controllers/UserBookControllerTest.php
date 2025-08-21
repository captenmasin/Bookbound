<?php

use App\Models\Book;
use App\Models\User;
use App\Enums\UserBookStatus;
use Tests\Concerns\GiveSubscription;
use Inertia\Testing\AssertableInertia;

uses(GiveSubscription::class);

describe('UserBookController', function () {
    test('guests are redirected to the login page', function () {
        $response = $this->get('/books');
        $response->assertRedirect('/login');
    });

    test('authenticated users can visit the books page', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/books');
        $response->assertStatus(200);
    });

    test('books page is displayed correctly', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('user.books.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('books/Index')
            ->has('books')
        );
    });

    test('books page shows user books', function () {
        $user = User::factory()->create();
        $books = Book::factory()->count(3)->create();

        // Add books to user's library
        foreach ($books as $book) {
            $user->books()->attach($book, [
                'status' => UserBookStatus::Read->value,
            ]);
        }

        $response = $this->actingAs($user)
            ->get(route('user.books.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('books/Index')
            ->has('books', 3)
        );
    });

    test('books page can be filtered by status', function () {
        $user = User::factory()->create();
        $completedBooks = Book::factory()->count(2)->create();
        $planToReadBooks = Book::factory()->count(3)->create();

        // Add books with different statuses
        foreach ($completedBooks as $book) {
            $user->books()->attach($book, [
                'status' => UserBookStatus::Read->value,
            ]);
        }

        foreach ($planToReadBooks as $book) {
            $user->books()->attach($book, [
                'status' => UserBookStatus::PlanToRead->value,
            ]);
        }

        $response = $this->actingAs($user)
            ->get(route('user.books.index', ['status' => UserBookStatus::Read->value]));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('books/Index')
            ->has('books', 2)
        );
    });

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
                'status' => UserBookStatus::Read->name,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('book_user', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => UserBookStatus::Read->name,
        ]);
    });

    test('status update fails when book is missing', function () {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->patch(route('api.user.books.update_status', $book), [
            'status' => UserBookStatus::Read->name,
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
});
