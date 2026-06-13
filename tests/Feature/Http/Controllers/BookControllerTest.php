<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use App\Models\Rating;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;
use App\Actions\Books\ImportBookFromData;
use App\Contracts\BookApiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

uses(RefreshDatabase::class, MockeryPHPUnitIntegration::class);

describe('BookController', function () {
    it('shows a single book', function () {
        $book = Book::factory()->create();

        $response = $this->get(route('books.show', $book));

        $response->assertSuccessful();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('books/Show')
            ->has('book')
        );
    });

    it('loads deferred related books and reviews on a partial reload', function () {
        Cache::flush();

        $user = User::factory()->create();
        $reviewer = User::factory()->create();
        $author = Author::factory()->create();
        $tag = Tag::factory()->create();
        $book = Book::factory()->create(['title' => 'Carrie']);
        $relatedByAuthor = Book::factory()->create(['title' => 'The Shining']);
        $relatedByTag = Book::factory()->create(['title' => 'Pet Sematary']);
        Book::factory()->create(['title' => 'Pride and Prejudice']);

        $book->authors()->attach($author);
        $book->tags()->attach($tag);
        $relatedByAuthor->authors()->attach($author);
        $relatedByTag->tags()->attach($tag);

        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $reviewer->id,
        ]);

        Rating::factory()->create([
            'book_id' => $book->id,
            'user_id' => $reviewer->id,
            'value' => 5,
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.show', $book));

        $response->assertSuccessful();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('books/Show')
            ->reloadOnly(['related', 'reviews'], fn (AssertableInertia $page) => $page
                ->has('related', 2)
                ->has('reviews', 1)
                ->where('related.0.id', $relatedByAuthor->id)
                ->where('related.1.id', $relatedByTag->id)
                ->missing('related.2')
            )
        );
    });

    it('redirects preview to show when book exists', function () {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.preview', $book->identifier));

        $response->assertRedirect(route('books.show', $book));
    });

    it('shows preview and queues import when book is new', function () {
        $identifier = '9780747532743';
        $user = User::factory()->create();

        $mock = Mockery::mock(BookApiServiceInterface::class);
        $mock->shouldReceive('get')
            ->with($identifier)
            ->andReturn([
                'isbn' => $identifier,
                'title' => 'Harry Potter',
                'authors' => [
                    'J.K. Rowling',
                ],
                'published_date' => '1997-06-26',
                'description' => 'A young wizard embarks on an adventure.',
                'pageCount' => 223,
                'cover' => 'https://example.com/cover.jpg',
                'codes' => [
                    ['type' => 'ISBN_13', 'identifier' => '9780747532743'],
                    ['type' => 'ISBN_10', 'identifier' => '0747532745'],
                ],
            ]);

        app()->instance(BookApiServiceInterface::class, $mock);

        Queue::fake();

        $response = $this->actingAs($user)
            ->get(route('books.preview', ['identifier' => $identifier]));

        ImportBookFromData::assertPushed(1);

        $response->assertSuccessful();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('books/Preview')
            ->where('identifier', $identifier)
        );
    });

    it('shows the search page for authenticated users', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.search'));

        $response->assertSuccessful();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('books/Search')
            ->has('initialQuery')
            ->has('page')
            ->has('perPage')
        );
    });

    it('stores previous searches with their type', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.search', ['q' => 'harry potter']));

        $response->assertSuccessful();

        $this->assertDatabaseHas('previous_searches', [
            'user_id' => $user->id,
            'search_term' => 'harry potter',
            'type' => 'query',
        ]);
    });

    it('redirects guests to login for search page', function () {
        $response = $this->get(route('books.search'));

        $response->assertRedirect(route('login'));
    });
});
