<?php

namespace Database\Factories;

use Str;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(rand(2, 6));
        $words = explode(' ', $title);
        $chunks = array_chunk($words, 2);
        $coverTitle = array_map(fn ($pair) => implode(' ', $pair), $chunks);
        $coverTitle = implode('\n', $coverTitle);

        $mainColour = str_replace('#', '', $this->faker->hexColor);

        return [
            'title' => $title,
            'description' => implode('\n\n', $this->faker->paragraphs(5)),
            'published_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'settings' => [
                'colour' => '#'.$mainColour,
            ],
            'identifier' => Str::random(),
            'path' => $this->faker->slug(),
            'codes' => [
                [
                    'type' => 'ISBN_13',
                    'identifier' => $this->faker->isbn13(),
                ],
                [
                    'type' => 'ISBN_10',
                    'identifier' => $this->faker->isbn10(),
                ],
            ],
            'page_count' => rand(0, 2000),
            'service' => 'local',
            'publisher_id' => null,
            'original_cover' => 'https://placehold.co/500x800/'.$mainColour.'/'.str_replace('#', '', $this->faker->hexColor).'?text='.urlencode($coverTitle),
            'edition' => ['Illustrated', 'Paperback', 'Hardcover', 'Audiobook', 'Ebook', ''][rand(0, 5)],
            'binding' => ['Print', 'Paperback', 'Audiobook', 'Ebook', ''][rand(0, 4)],
            'language' => $this->faker->languageCode,
        ];
    }
}
