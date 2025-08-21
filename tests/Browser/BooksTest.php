<?php

use App\Models\Book;
use App\Models\User;
use App\Models\Rating;
use App\Models\Review;

use function Pest\Laravel\actingAs;

// Guest should be able to view a single book page

test('guest can view book details', function () {
    $book = Book::factory()->create(['title' => 'Guest Accessible Book']);

    visit(route('books.show', $book))
        ->assertSee('Guest Accessible Book');
});

// Logged in user should see their library books on the books page
test('user views books in grid by default', function () {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books);

    actingAs($user);

    visit('/books')
        ->assertSee('Your Library')
        ->assertAriaAttribute('.desktop-book-view-tabs [role="tab"]:nth-of-type(1)', 'selected', 'true')
        ->assertCount('.book-card', $books->count());
});

test('user books view is based on settings', function () {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books);

    $user->settings()->set('library.view', 'list');

    actingAs($user);

    visit('/books')
        ->assertSee('Your Library')
        ->assertAriaAttribute('.desktop-book-view-tabs [role="tab"]:nth-of-type(2)', 'selected', 'true')
        ->assertCount('.book-card-horizontal', $books->count());
});

test('user grid view renders correctly', function () {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books);

    actingAs($user);

    visit('/books')
        ->assertSee('Your Library')
        ->click('.desktop-book-view-tabs [role="tab"]:nth-of-type(1)')
        ->assertCount('.book-card', 2)
        ->assertCount('.book-card-horizontal', 0)
        ->assertCount('.book-card-shelf-item', 0)
        ->assertSee($books[0]->title)
        ->assertSee($books[1]->title);
});

test('user list view renders correctly', function () {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books);

    actingAs($user);

    visit('/books')
        ->assertSee('Your Library')
        ->click('.desktop-book-view-tabs [role="tab"]:nth-of-type(2)')
        ->assertCount('.book-card', 0)
        ->assertCount('.book-card-horizontal', 2)
        ->assertCount('.book-card-shelf-item', 0)
        ->assertSee($books[0]->title)
        ->assertSee($books[1]->title);
});

test('user shelf view renders correctly', function () {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books);

    actingAs($user);

    visit('/books')
        ->assertSee('Your Library')
        ->click('.desktop-book-view-tabs [role="tab"]:nth-of-type(3)')
        ->assertCount('.book-card', 0)
        ->assertCount('.book-card-horizontal', 0)
        ->assertCount('.book-card-shelf-item', 2)
        ->assertSee($books[0]->title)
        ->assertSee($books[1]->title);
});

// Logged in user can search
test('user can search for a book', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/books/search')
        ->type('#query', 'harry potter')
        ->click('#searchSubmit')
        ->assertSee('No books found');

    $book = Book::factory()->create(['title' => 'Harry Potter']);
    visit('/books/search')
        ->type('#query', 'harry potter')
        ->click('#searchSubmit')
        ->assertSee('Harry Potter');
});

// Users can add a book to their library
test('user can add a book to their library', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    actingAs($user);

    visit(route('books.show', $book))
        ->click('[data-slot="select-trigger"]')
        ->click('[data-slot="select-item"]:first-of-type')
        ->assertSee('Added to your library');

    $this->assertDatabaseHas('book_user', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => 'Plan to Read',
    ]);
});

// Users can change a book status
test('user can change book status', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book, ['status' => 'Plan to Read']);

    actingAs($user);

    visit(route('books.show', $book))
        ->click('[data-slot="select-trigger"]')
        ->click('[data-slot="select-item"]:nth-of-type(3)')
        ->assertSee('Book status updated successfully');

    $this->assertDatabaseHas('book_user', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => 'Read',
    ]);
});

// Users can add a note to a book
test('user can add a note to a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book);

    actingAs($user);

    visit(route('books.show', $book))
        ->type('#noteInput', 'A new note')
        ->press('Save')
        ->assertSee('A new note');

    $this->assertDatabaseHas('notes', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'content' => 'A new note',
    ]);
})->todo('fix this test');

// Users can delete a note on a book

test('user can delete a note on a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book);

    $note = \App\Models\Note::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'content' => 'Delete me',
    ]);

    actingAs($user);

    visit(route('books.show', $book))
        ->click('.book-display-type [role="tab"]:nth-of-type(1)')
        ->assertSee('Delete me')
        ->press('#delete-note-'.$note->id)
        ->assertSee('Are you sure you want to delete this note?')
        ->press('Confirm')
        ->assertDontSee('Delete me');

    $this->assertDatabaseMissing('notes', [
        'id' => $note->id,
    ]);
});

// Users can add a review to a book
test('user can add a review to a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book);

    actingAs($user);

    visit(route('books.show', $book))
        ->click('.book-display-type [role="tab"]:nth-of-type(2)')
        ->press('Write a review')
        ->type('#reviewTitle', 'A new review')
        ->type('#reviewContent', 'Content here')
        ->press('Submit Review')
        ->assertSee('A new review');

    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'title' => 'A new review',
        'content' => 'Content here',
    ]);
});

// Users can update a review on a book
test('user can update a review on a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'title' => 'Old Title',
        'content' => 'Old content',
    ]);

    $user->books()->attach($book);

    actingAs($user);

    visit(route('books.show', $book))
        ->click('.book-display-type [role="tab"]:nth-of-type(2)')
        ->press('Edit review')
        ->type('#reviewTitle', 'Updated Title')
        ->type('#reviewContent', 'Updated content')
        ->press('Update Review')
        ->assertSee('Updated Title');

    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'title' => 'Updated Title',
        'content' => 'Updated content',
    ]);
});

// Users can delete a review on a book
test('user can delete a review on a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book);

    $review = Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'title' => 'Delete Review',
    ]);

    actingAs($user);

    visit(route('books.show', $book))
        ->click('.book-display-type [role="tab"]:nth-of-type(2)')
        ->click('#delete-review-'.$review->id)
        ->assertSee('Are you sure you want to delete this review?')
        ->press('Confirm');

    $this->assertDatabaseMissing('reviews', [
        'id' => $review->id,
    ]);
});

test('user cannot see delete button on other users reviews', function () {
    $user = User::factory()->create();
    $secondUser = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book);
    $secondUser->books()->attach($book);

    Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'title' => 'Delete Review',
    ]);

    Review::factory()->create([
        'user_id' => $secondUser->id,
        'book_id' => $book->id,
        'title' => 'Delete Review',
    ]);

    actingAs($user);

    visit(route('books.show', $book))
        ->click('.book-display-type [role="tab"]:nth-of-type(2)')
        ->assertMissing('#delete-review-2')
        ->assertPresent('#delete-review-1');
});

// Users can update their rating of a book
test('user can update book rating', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book);

    actingAs($user);

    visit(route('books.show', $book))
        ->assertSee('Your rating')
        ->click('[aria-label="Rate 4 star"]')
        ->click('[aria-label="Rate 2 star"]')
        ->assertSee('Rating updated successfully');

    $this->assertDatabaseHas('ratings', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'value' => 2,
    ]);
})->todo('broken on github actions');
