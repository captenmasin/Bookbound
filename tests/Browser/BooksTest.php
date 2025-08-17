<?php

use App\Models\Book;
use App\Models\User;
use App\Models\Rating;
use App\Models\Review;
use Laravel\Dusk\Browser;

// Guest should be able to view a single book page

test('guest can view book details', function () {
    $book = Book::factory()->create(['title' => 'Guest Accessible Book']);

    $this->browse(function (Browser $browser) use ($book) {
        $browser->visit(route('books.show', $book))
            ->assertSee('Guest Accessible Book');
    });
});

// Logged in user should see their library books on the books page
test('user views books in grid by default', function () {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books);

    $this->browse(function (Browser $browser) use ($user, $books) {
        $browser->loginAs($user)
            ->visit('/books')
            ->assertSee('Your Library');

        $tabButtons = $browser->elements('.desktop-book-view-tabs [role="tab"]');
        $this->assertTrue($tabButtons[0]->getAttribute('aria-selected') === 'true');

        $bookCardElements = $browser->elements('.book-card');
        $this->assertCount(count($books), $bookCardElements);
    });
});

test('user books view is based on settings', function () {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books);

    $user->settings()->set('library.view', 'list');

    $this->browse(function (Browser $browser) use ($user, $books) {
        $browser->loginAs($user)
            ->visit('/books')
            ->assertSee('Your Library');

        $tabButtons = $browser->elements('.desktop-book-view-tabs [role="tab"]');
        $this->assertTrue($tabButtons[1]->getAttribute('aria-selected') === 'true');

        $bookCardElements = $browser->elements('.book-card-horizontal');
        $this->assertCount(count($books), $bookCardElements);
    });
});

test('user grid view renders correctly', function () {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books);

    $this->browse(function (Browser $browser) use ($user, $books) {
        $browser->loginAs($user)
            ->visit('/books')
            ->waitFor('.desktop-book-view-tabs')
            ->assertSee('Your Library');

        // Get ShadCN tab buttons (via role="tab")
        $tabButtons = $browser->elements('.desktop-book-view-tabs [role="tab"]');

        // Click first tab (grid view)
        $tabButtons[0]->click();

        // Wait for DOM to update
        $browser->pause(250)
            ->waitUntilMissing('.book-card-horizontal')
            ->waitUntilMissing('.book-card-shelf-item')
            ->waitFor('.book-card');

        // Assert correct cards visible
        $this->assertCount(2, $browser->elements('.book-card'));
        $this->assertCount(0, $browser->elements('.book-card-horizontal'));
        $this->assertCount(0, $browser->elements('.book-card-shelf-item'));

        $bookCardElements = $browser->elements('.book-card');

        foreach ($bookCardElements as $index => $element) {
            $browser->mouseover('#'.$element->getAttribute('id'))
                ->waitForText($books[$index]->title)
                ->assertSee($books[$index]->title);
        }
    });
});

test('user list view renders correctly', function () {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books);

    $this->browse(function (Browser $browser) use ($user, $books) {
        $browser->loginAs($user)
            ->visit('/books')
            ->waitFor('.desktop-book-view-tabs')
            ->assertSee('Your Library');

        // Get ShadCN tab buttons (via role="tab")
        $tabButtons = $browser->elements('.desktop-book-view-tabs [role="tab"]');

        // Click second tab (grid view)
        $tabButtons[1]->click();

        // Wait for DOM to update
        $browser->pause(250)
            ->waitUntilMissing('.book-card')
            ->waitUntilMissing('.book-card-shelf-item')
            ->waitFor('.book-card-horizontal');

        // Assert correct cards visible
        $this->assertCount(0, $browser->elements('.book-card'));
        $this->assertCount(2, $browser->elements('.book-card-horizontal'));
        $this->assertCount(0, $browser->elements('.book-card-shelf-item'));

        $bookCardElements = $browser->elements('.book-card-horizontal');

        foreach ($bookCardElements as $index => $element) {
            $browser->assertSee($books[$index]->title);
        }
    });
});

test('user shelf view renders correctly', function () {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books);

    $this->browse(function (Browser $browser) use ($user, $books) {
        $browser->loginAs($user)
            ->visit('/books')
            ->waitFor('.desktop-book-view-tabs')
            ->assertSee('Your Library');

        // Get ShadCN tab buttons (via role="tab")
        $tabButtons = $browser->elements('.desktop-book-view-tabs [role="tab"]');

        // Click first tab (list view)
        $tabButtons[2]->click();

        // Wait for DOM to update
        $browser->pause(250)
            ->waitUntilMissing('.book-card')
            ->waitUntilMissing('.book-card-horizontal')
            ->waitFor('.book-card-shelf-item');

        // Assert correct cards visible
        $this->assertCount(0, $browser->elements('.book-card'));
        $this->assertCount(0, $browser->elements('.book-card-horizontal'));
        $this->assertCount(2, $browser->elements('.book-card-shelf-item'));

        $bookCardElements = $browser->elements('.book-card-shelf-item');

        foreach ($bookCardElements as $index => $element) {
            $browser->assertSee($books[$index]->title);
        }
    });
});

// Logged in user can search
test('user can search for a book', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/books/search')
            ->type('#query', 'harry potter')
            ->press('Search')
            ->waitForText('Searching')
            ->assertSee('Searching');
    });
});

// Users can add a book to their library
test('user can add a book to their library', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $this->browse(function (Browser $browser) use ($user, $book) {
        $browser->loginAs($user)
            ->visit(route('books.show', $book))
            ->click('[data-slot="select-trigger"]')
            ->click('[data-slot="select-item"]:first-of-type')
            ->pause(500);
    });

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

    $this->browse(function (Browser $browser) use ($user, $book) {
        $browser->loginAs($user)
            ->visit(route('books.show', $book))
            ->click('[data-slot="select-trigger"]')
            ->click('[data-slot="select-item"]:nth-of-type(3)')
            ->pause(500);
    });

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

    $this->browse(function (Browser $browser) use ($user, $book) {
        $browser->loginAs($user)
            ->visit(route('books.show', $book))
            ->type('#noteInput', 'A new note')
            ->press('Save')
            ->waitForText('A new note', 5);
    });

    $this->assertDatabaseHas('notes', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'content' => 'A new note',
    ]);
})->todo();

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

    $this->browse(function (Browser $browser) use ($user, $book, $note) {
        $browser->loginAs($user)
            ->visit(route('books.show', $book))
            ->waitFor('.book-display-type');

        $displayTabs = $browser->elements('.book-display-type [role="tab"]');
        $displayTabs[0]->click();

        $browser->waitFor('.notes-section')
            ->assertSee('Delete me')
            ->pressAndWaitFor('#delete-note-'.$note->id)
            ->waitForText('Are you sure you want to delete this note?')
            ->press('Confirm')
            ->pause(500)
            ->assertDontSee('Delete me');
    });

    $this->assertDatabaseMissing('notes', [
        'id' => $note->id,
    ]);
});

// Users can add a review to a book
test('user can add a review to a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book);

    $this->browse(function (Browser $browser) use ($user, $book) {
        $browser->loginAs($user)
            ->visit(route('books.show', $book))
            ->waitFor('.book-display-type');

        $displayTabs = $browser->elements('.book-display-type [role="tab"]');
        $displayTabs[1]->click();

        $browser->press('Write a review')
            ->type('#reviewTitle', 'A new review')
            ->type('#reviewContent', 'Content here')
            ->press('Submit Review')
            ->waitForText('A new review', 5);
    });

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

    $this->browse(function (Browser $browser) use ($user, $book) {
        $browser->loginAs($user)
            ->visit(route('books.show', $book))
            ->waitFor('.book-display-type');

        $displayTabs = $browser->elements('.book-display-type [role="tab"]');
        $displayTabs[1]->click();

        $browser->press('Edit review')
            ->type('#reviewTitle', 'Updated Title')
            ->type('#reviewContent', 'Updated content')
            ->press('Update Review')
            ->waitForText('Updated Title', 5);
    });

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

    $this->browse(function (Browser $browser) use ($user, $book, $review) {
        $browser->loginAs($user)
            ->visit(route('books.show', $book))
            ->waitFor('.book-display-type');

        $displayTabs = $browser->elements('.book-display-type [role="tab"]');
        $displayTabs[1]->click();

        $browser
            ->click('#delete-review-'.$review->id)
            ->waitForText('Are you sure you want to delete this review?')
            ->press('Confirm')
            ->pause(500);
    });

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

    $this->browse(function (Browser $browser) use ($user, $book) {
        $browser->loginAs($user)
            ->visit(route('books.show', $book))
            ->waitFor('.book-display-type');

        $displayTabs = $browser->elements('.book-display-type [role="tab"]');
        $displayTabs[1]->click();

        $browser->waitFor('.reviews-section')
            ->assertMissing('#delete-review-2')
            ->assertPresent('#delete-review-1');
    });
});

// Users can update their rating of a book

test('user can update book rating', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book);

    $this->browse(function (Browser $browser) use ($user, $book) {
        $browser->loginAs($user)
            ->visit(route('books.show', $book))
            ->click('button[aria-label="Rate 4 star"]')
            ->pause(500)
            ->click('button[aria-label="Rate 2 star"]')
            ->pause(500);
    });

    $this->assertDatabaseHas('ratings', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'value' => 2,
    ]);
});
