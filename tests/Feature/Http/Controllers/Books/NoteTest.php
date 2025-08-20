<?php

use App\Models\Book;
use App\Models\Note;
use App\Models\User;
use App\Enums\UserBookStatus;
use Inertia\Testing\AssertableInertia;

uses(Tests\Concerns\GiveSubscription::class);

test('non-subscribed user cannot add a note to a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('notes.store', $book->path), [
            'content' => 'My personal note',
        ]);

    $response->assertRedirectBack();
    $response->assertSessionHas('error', 'Your current plan does not allow adding notes.');
    $this->assertDatabaseMissing('notes', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'content' => 'My personal note',
    ]);
});

test('subscribed user cannot add a note to a book not in their library', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $this->giveActiveSubscription($user, config('subscriptions.plans.pro.key'));

    $response = $this->actingAs($user)
        ->post(route('notes.store', $book->path), [
            'content' => 'My personal note',
        ]);

    $response->assertRedirectBack();
    $response->assertSessionHas('error', 'You can only add notes to books in your library.');
    $this->assertDatabaseMissing('notes', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'content' => 'My personal note',
    ]);
});

test('subscribed user can add a note to a book in their library', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book->id, ['status' => UserBookStatus::Reading]);

    $this->giveActiveSubscription($user, config('subscriptions.plans.pro.key'));

    $response = $this->actingAs($user)
        ->post(route('notes.store', $book->path), [
            'content' => 'My personal note',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('notes', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'content' => 'My personal note',
        'book_status' => UserBookStatus::Reading,
    ]);
});

test('note content is required', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->books()->attach($book->id, ['status' => UserBookStatus::Reading]);

    $response = $this->actingAs($user)
        ->post(route('notes.store', $book->path), [
            'content' => '',
        ]);

    $response->assertSessionHasErrors('content');
    $this->assertDatabaseMissing('notes', [
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);
});

// Test that a user can delete their note
test('user can delete their note', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $note = Note::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'content' => 'Delete me',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('notes.destroy', [$book->path, $note->id]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('notes', ['id' => $note->id]);
});

// Test that a user cannot delete someone else's note
test("user cannot delete another user's note", function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $book = Book::factory()->create();

    $note = Note::factory()->create([
        'user_id' => $otherUser->id,
        'book_id' => $book->id,
        'content' => 'Should not delete',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('notes.destroy', [$book->path, $note->id]));

    $response->assertForbidden();
    $this->assertDatabaseHas('notes', ['id' => $note->id]);
});

// Test that storing notes requires authentication
test('notes require authentication', function () {
    $book = Book::factory()->create();
    $note = Note::factory()->create([
        'user_id' => User::factory()->create()->id,
        'book_id' => $book->id,
        'content' => 'content',
    ]);

    $this->post(route('notes.store', $book->path), ['content' => 'guest note'])
        ->assertRedirect(route('login'));

    $this->delete(route('notes.destroy', [$book->path, $note->id]))
        ->assertRedirect(route('login'));
});

test('user can view their notes', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    Note::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'content' => 'My personal note',
    ]);

    $this->actingAs($user)
        ->get(route('user.notes.index'))
        ->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user/Notes')
            ->has('notes')
            ->has('notes.meta'));
});
