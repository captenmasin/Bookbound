<?php

use App\Models\Book;
use App\Models\User;
use App\Models\Rating;
use App\Models\Review;

describe('RatingController', function () {
    test('user can create book rating', function () {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->books()->attach($book);

        $ratingData = [
            'rating' => [
                'value' => 4,
            ],
        ];

        $response = $this->actingAs($user)
            ->post(route('ratings.store', $book), $ratingData);

        $response->assertRedirect();

        $this->assertDatabaseHas('ratings', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'value' => 4,
        ]);
    });

    test('user cannot create book rating when book not in library', function () {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $ratingData = [
            'rating' => [
                'value' => 4,
            ],
        ];

        $response = $this->actingAs($user)
            ->post(route('ratings.store', $book), $ratingData);

        $response->assertForbidden();
    });

    test('user can re-rate book', function () {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->books()->attach($book);

        // Create initial rating
        $rating = Rating::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // Try to create another rating
        $this->actingAs($user)
            ->put(route('ratings.update', ['book' => $book, 'rating' => $rating]), [
                'rating' => [
                    'id' => $rating->id,
                    'value' => 5,
                ],
            ]);

        // Verify only one review exists
        $this->assertDatabaseCount('ratings', 1);

        // Verify the review was updated
        $this->assertDatabaseHas('ratings', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'value' => 5,
        ]);
    });

    test('user can delete their rating', function () {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->books()->attach($book);

        $rating = Rating::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('ratings.destroy', ['book' => $book, 'rating' => $rating]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('ratings', ['id' => $rating->id]);
    });

    test('user cannot delete others rating', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $rating = Rating::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->delete(route('ratings.destroy', ['book' => $book, 'rating' => $rating]));

        $response->assertForbidden();

        $this->assertDatabaseHas('ratings', ['id' => $rating->id]);
    });

    test('ratings require authentication', function () {
        $book = Book::factory()->create();

        $response = $this->post(route('ratings.store', $book), [
            'rating' => [
                'value' => 4,
            ],
        ]);

        $response->assertRedirect(route('login'));
    });
});
