<?php

use App\Models\Book;
use App\Models\User;
use App\Enums\UserBookStatus;
use Inertia\Testing\AssertableInertia;
use App\Actions\Books\ImportBookFromData;
use App\Contracts\BookApiServiceInterface;

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

test('single book details can be viewed', function () {
    $book = Book::factory()->create();

    $response = $this->get(route('books.show', $book));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('books/Show')
        ->has('book')
    );
});

test('single book preview redirects if book exists', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('books.preview', $book->identifier));

    $response->assertRedirect(route('books.show', $book));
});

test('single book preview displays for new book', function () {
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

    $this->app->instance(BookApiServiceInterface::class, $mock);

    \Illuminate\Support\Facades\Queue::fake();

    $response = $this->actingAs($user)
        ->get(route('books.preview', ['identifier' => $identifier]));

    ImportBookFromData::assertPushed(1);

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('books/Preview')
        ->where('identifier', $identifier)
    );
});
