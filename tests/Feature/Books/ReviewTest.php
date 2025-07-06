<?php

use App\Models\Book;
use App\Models\User;
use App\Models\Review;

test('user can create book review', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $reviewData = [
        'rating' => 4,
        'content' => 'This is a great book that I highly recommend.',
        'title' => 'Great Read',
    ];

    $response = $this->actingAs($user)
        ->post(route('reviews.store', $book), $reviewData);

    $response->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'rating' => 4,
        'content' => 'This is a great book that I highly recommend.',
        'title' => 'Great Read',
    ]);
});

test('user can review same book again but it will just update', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    // Create initial review
    Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'rating' => 5,
        'content' => 'Initial review',
        'title' => 'First Review',
    ]);

    // Try to create another review
    $this->actingAs($user)
        ->post(route('reviews.store', $book), [
            'rating' => 3,
            'content' => 'Second attempt at reviewing',
            'title' => 'Second Review',
        ]);

    // Verify only one review exists
    $this->assertDatabaseCount('reviews', 1);

    // Verify the review was updated
    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'rating' => 3,
        'content' => 'Second attempt at reviewing',
        'title' => 'Second Review',
    ]);
});

test('user can delete their review', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $review = Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('reviews.destroy', ['book' => $book, 'review' => $review]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

test('user cannot delete others review', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $book = Book::factory()->create();

    $review = Review::factory()->create([
        'user_id' => $otherUser->id,
        'book_id' => $book->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('reviews.destroy', ['book' => $book, 'review' => $review]));

    $response->assertForbidden();
    $this->assertDatabaseHas('reviews', ['id' => $review->id]);
});

test('reviews require authentication', function () {
    $book = Book::factory()->create();

    $response = $this->post(route('reviews.store', $book), [
        'rating' => 4,
        'content' => 'Test review',
        'title' => 'Test',
    ]);

    $response->assertRedirect(route('login'));
});
