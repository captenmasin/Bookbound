<?php

namespace Database\Factories;

use App\Models\GoodreadsImport;
use App\Models\GoodreadsImportFailure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoodreadsImportFailure>
 */
class GoodreadsImportFailureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goodreads_import_id' => GoodreadsImport::factory(),
            'row_number' => $this->faker->numberBetween(2, 200),
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'reason' => 'Unable to match this Goodreads row to a book.',
            'raw_row' => [
                'Title' => $this->faker->sentence(3),
                'Author' => $this->faker->name(),
            ],
        ];
    }
}
