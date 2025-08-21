<?php

namespace App\Actions\Books;

use Cache;
use App\Models\Tag;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use Illuminate\Support\Collection;
use App\Transformers\BookTransformer;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Contracts\BookApiServiceInterface;

class ImportBookFromData
{
    use AsAction;

    public function __construct(protected BookApiServiceInterface $booksApi) {}

    public function handle(
        null|string|array $data = null,
        bool $force = false,
        array $cache = [] // ['authors' => Collection, 'tags' => Collection, 'publishers' => Collection]
    ): Book {
        if (is_string($data)) {
            $identifier = $data;
            $data = Cache::pull('book:'.$identifier) ?? null;
        } else {
            $identifier = $data['identifier'] ?? $data['isbn13'] ?? $data['isbn'] ?? null;
        }

        if (empty($identifier)) {
            throw new \InvalidArgumentException('Identifier is required to import a book.');
        }

        if (! $force && ($existing = Book::where('identifier', $identifier)->first())) {
            return $existing;
        }

        if (empty($data)) {
            $data = Cache::remember("books:id:$identifier", now()->addWeek(), function () use ($identifier) {
                return BookTransformer::fromIsbn($this->booksApi->get($identifier));
            });
            if (empty($data)) {
                throw new \Exception("No data found for identifier: $identifier");
            }
        }

        $book = Book::create([
            'identifier' => $identifier,
            'codes' => $data['codes'],
            'edition' => $data['edition'] ?? null,
            'page_count' => $data['pageCount'] ?? null,
            'title' => $data['title'],
            'published_date' => $data['published_date'],
            'description' => $data['description'],
            'service' => $data['service'] ?? $this->booksApi::ServiceName,
            'original_cover' => $data['cover'] ?? null,
            'binding' => $data['binding'] ?? null,
            'language' => $data['language'] ?? null,
        ]);

        if (isset($data['cover_large']) || isset($data['cover'])) {
            ImportBookCover::dispatch($book, $data['cover_large'] ?? $data['cover']);
        }

        // Authors
        if (! empty($data['authors'])) {
            $authorNames = collect($data['authors'])->filter(fn ($a) => isset($a['name']))
                ->pluck('name')->unique()->values();

            $existing = $cache['authors'] ?? collect();

            $authorIds = $authorNames->map(function ($name) use ($existing) {
                return $existing[$name]?->id ?? Author::firstOrCreate(['name' => $name])->id;
            });

            $book->authors()->syncWithoutDetaching($authorIds);
        }

        // Tags
        if (! empty($data['tags'])) {
            $tagNames = collect($data['tags'])
                ->flatMap(fn ($path) => array_map('trim', explode('/', $path)))
                ->unique()
                ->values();

            $existing = $cache['tags'] ?? collect();

            $tagIds = $tagNames->map(function ($name) use ($existing) {
                return $existing[$name]?->id ?? Tag::firstOrCreate(['name' => $name])->id;
            });

            $book->tags()->syncWithoutDetaching($tagIds);
        }

        // Publisher
        if (! empty($data['publisher'])) {
            $name = is_array($data['publisher']) ? $data['publisher']['name'] ?? null : $data['publisher'];

            if ($name) {
                $existing = $cache['publishers'] ?? collect();
                $publisher = $existing[$name] ?? Publisher::firstOrCreate(['name' => $name]);

                $book->publisher()->associate($publisher)->save();
            }
        }

        return $book;
    }
}
