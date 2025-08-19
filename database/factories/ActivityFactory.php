<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'type' => fake()->randomElement([
                'book.added',
                'book.status.updated',
                'book.removed',
                'book.note.added',
                'book.note.updated',
                'book.note.removed',
                'book.review.added',
                'book.review.updated',
                'book.review.removed',
                'book.cover.updated',
                'book.cover.removed',
            ]),
            'properties' => [
                'book_title' => fake()->sentence(3),
                'status' => fake()->randomElement(['Want to Read', 'Currently Reading', 'Read']),
                'rating' => fake()->numberBetween(1, 5),
            ],
        ];
    }
}
