<?php

namespace App\Transformers;

use App\Models\Book;
use Illuminate\Support\Str;
use App\Services\ISBNdbService;

class BookTransformer
{
    public static function fromIsbn(array $data): array
    {
        $identifier = $data['isbn13'] ?? $data['isbn'] ?? $data['isbn10'] ?? null;

        $existing = Book::where('identifier', $identifier)->first();
        $links = $existing
            ? ['show' => route('books.show', $existing)]
            : ['show' => route('books.preview', ['identifier' => $identifier])];

        $subjects = collect($data['subjects'] ?? [])
            ->map(fn ($s) => str_replace('_', ' ', trim(Str::before($s, '--'))))->flatten()
            ->map(fn ($s) => explode(', ', $s))->flatten()
            ->map(fn ($s) => explode('& ', $s))->flatten()
            ->map(fn ($s) => trim($s))->flatten()
            ->map(fn ($s) => Str::headline($s))->flatten()
            ->unique()->values()->all();

        $authors = collect($data['authors'] ?? [])
            ->map(fn ($author) => [
                'name' => $author ?? 'Unknown',
                'uuid' => null,
            ])->values()->toArray();

        $description = $data['overview'] ?? $data['synopsis'] ?? $data['description'] ?? null;

        return [
            'id' => $data['id'] ?? null,
            'identifier' => $identifier,
            'codes' => [
                ['type' => 'ISBN_13', 'identifier' => $data['isbn13'] ?? null],
                ['type' => 'ISBN_10', 'identifier' => $data['isbn'] ?? null],
            ],
            'title' => $data['title'] ?? null,
            'pageCount' => $data['pages'] ?? null,
            'tags' => $subjects ?? null,
            'publisher' => ! empty($data['publisher']) ? [
                'name' => $data['publisher'],
                'uuid' => null,
            ] : null,
            'description' => $description,
            'description_clean' => strip_tags($description ?? ''),
            'authors' => $authors,
            'edition' => $data['edition'] ?? null,
            'binding' => $data['binding'] ?? null,
            'language' => $data['language'] ?? null,
            'published_date' => $data['date_published'] ?? null,
            'cover' => $data['image'] ?? null,
            'cover_large' => $data['image_original'] ?? null,
            'service' => ISBNdbService::ServiceName,
            'links' => $links,
        ];
    }
}
