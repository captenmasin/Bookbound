<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use App\Models\Activity;
use App\Enums\UserBookStatus;

use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;

describe('DashboardController', function () {
    it('redirects guests to the login page', function () {
        $response = get('/dashboard');
        $response->assertRedirect('/login');
    });

    it('allows authenticated users to view their dashboard', function () {
        $user = User::factory()->create();
        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
    });

    it('displays user information on the dashboard', function () {
        $user = User::factory()->create();
        actingAs($user);

        $response = get('/dashboard');
        $response->assertSee($user->name);
    });

    it('displays dashboard with books in different statuses', function () {
        $user = User::factory()->create();

        // Create books with different statuses
        $readingBook = Book::factory()->create();
        $readBook = Book::factory()->create();
        $planToReadBook = Book::factory()->create();

        $user->books()->attach($readingBook->id, ['status' => UserBookStatus::Reading->value]);
        $user->books()->attach($readBook->id, ['status' => UserBookStatus::Read->value]);
        $user->books()->attach($planToReadBook->id, ['status' => UserBookStatus::PlanToRead->value]);

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('statValues', fn ($statValues) => $statValues
                ->where('booksInLibrary', 3)
                ->where('completedBooks', 1)
                ->where('readingBooks', 1)
                ->where('planToRead', 1)
            )
        );
    });

    it('calculates statistics correctly with multiple books', function () {
        $user = User::factory()->create();

        $books = Book::factory()->count(10)->create();

        // Attach books with various statuses
        $user->books()->attach($books[0]->id, ['status' => UserBookStatus::Reading->value]);
        $user->books()->attach($books[1]->id, ['status' => UserBookStatus::Reading->value]);
        $user->books()->attach($books[2]->id, ['status' => UserBookStatus::Read->value]);
        $user->books()->attach($books[3]->id, ['status' => UserBookStatus::Read->value]);
        $user->books()->attach($books[4]->id, ['status' => UserBookStatus::Read->value]);
        $user->books()->attach($books[5]->id, ['status' => UserBookStatus::PlanToRead->value]);
        $user->books()->attach($books[6]->id, ['status' => UserBookStatus::PlanToRead->value]);
        $user->books()->attach($books[7]->id, ['status' => UserBookStatus::PlanToRead->value]);
        $user->books()->attach($books[8]->id, ['status' => UserBookStatus::PlanToRead->value]);
        $user->books()->attach($books[9]->id, ['status' => UserBookStatus::OnHold->value]);

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('statValues', fn ($statValues) => $statValues
                ->where('booksInLibrary', 10)
                ->where('completedBooks', 3)
                ->where('readingBooks', 2)
                ->where('planToRead', 4)
            )
        );
    });

    it('displays currently reading books section limited to 4 books', function () {
        $user = User::factory()->create();

        $readingBooks = Book::factory()->count(6)->create();

        foreach ($readingBooks as $book) {
            $user->books()->attach($book->id, ['status' => UserBookStatus::Reading->value]);
        }

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('currentlyReading', 4)
        );
    });

    it('displays top authors based on read books', function () {
        $user = User::factory()->create();

        // Create authors and books
        $author1 = Author::factory()->create();
        $author2 = Author::factory()->create();
        $author3 = Author::factory()->create();

        // Create books by different authors
        $books1 = Book::factory()->count(3)->create();
        $books2 = Book::factory()->count(2)->create();
        $books3 = Book::factory()->count(1)->create();

        // Attach authors to books
        foreach ($books1 as $book) {
            $book->authors()->attach($author1->id);
            $user->books()->attach($book->id, ['status' => UserBookStatus::Read->value]);
        }

        foreach ($books2 as $book) {
            $book->authors()->attach($author2->id);
            $user->books()->attach($book->id, ['status' => UserBookStatus::Read->value]);
        }

        foreach ($books3 as $book) {
            $book->authors()->attach($author3->id);
            $user->books()->attach($book->id, ['status' => UserBookStatus::Read->value]);
        }

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('authors')
            ->where('authors.0.name', $author1->name)
        );
    });

    it('displays top tags from user books', function () {
        $user = User::factory()->create();

        $tags = Tag::factory()->count(5)->create();
        $books = Book::factory()->count(3)->create();

        // Attach tags to books and books to user
        foreach ($books as $book) {
            $book->tags()->attach($tags->random(3)->pluck('id'));
            $user->books()->attach($book->id, ['status' => UserBookStatus::Read->value]);
        }

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('tags')
        );
    });

    it('displays recent user activities', function () {
        $user = User::factory()->create();

        $activities = Activity::factory()->count(10)->for($user)->create();

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('activities', 5)
        );
    });

    it('handles user with no books gracefully', function () {
        $user = User::factory()->create();
        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('statValues', fn ($statValues) => $statValues
                ->where('booksInLibrary', 0)
                ->where('completedBooks', 0)
                ->where('readingBooks', 0)
                ->where('planToRead', 0)
            )
            ->has('currentlyReading', 0)
            ->has('activities')
            ->has('tags', 0)
            ->has('authors', 0)
        );
    });

    it('orders books by most recently added to library', function () {
        $user = User::factory()->create();

        $oldBook = Book::factory()->create();
        $newBook = Book::factory()->create();

        // Add old book first
        $user->books()->attach($oldBook->id, [
            'status' => UserBookStatus::Reading->value,
            'created_at' => now()->subDays(5),
        ]);

        // Add new book later
        $user->books()->attach($newBook->id, [
            'status' => UserBookStatus::Reading->value,
            'created_at' => now(),
        ]);

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('currentlyReading', 2)
            ->where('currentlyReading.0.id', $newBook->id)
        );
    });
});
