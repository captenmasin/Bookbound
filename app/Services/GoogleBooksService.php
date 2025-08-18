<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Contracts\BookApiServiceInterface;

class GoogleBooksService implements BookApiServiceInterface
{
    public const string ServiceName = 'GoogleBooks';

    protected static string $baseUrl = 'https://www.googleapis.com/books/v1';

    protected const FIELDS = [
        'totalItems',
        'items' => [
            'id',
            'volumeInfo' => [
                'title',
                'pageCount',
                'publisher',
                'description',
                'authors',
                'publishedDate',
                'categories',
                'industryIdentifiers' => [
                    'type',
                    'identifier',
                ],
                'imageLinks',
            ],
        ],
    ];

    public static function search(
        ?string $query = null,
        ?string $author = null,
        ?string $subject = null,
        int $maxResults = 30,
        $page = 1): array
    {
        $query = $query.($author ? (' inauthor:"'.$author.'"') : '');
        ray('Google books query: '.$query);

        $query = trim($query);

        $page = max(0, $page - 1);

        $response = Http::get(self::$baseUrl.'/volumes', [
            'q' => $query,
            'startIndex' => $page * $maxResults,
            'fields' => self::buildFieldsString(),
            'langRestrict' => 'en',
            'printType' => 'books',
            'maxResults' => $maxResults,
            'country' => 'US',
        ]);

        $items = $response->json('items') ?? [];

        return [
            'total' => $response->json('totalItems', 0),
            'items' => collect($items)
                ->map(fn ($item) => self::transform($item))
                ->filter()
                ->all(),
        ];
    }

    public static function get(string $id): ?array
    {
        return self::transform(self::getFromApi($id));
    }

    public static function getByCode(string $isbn): ?array
    {
        $results = self::search("isbn:{$isbn}");

        return $results[0] ?? null;
    }

    public static function getFromApi(string $id): ?array
    {
        $response = Http::get(self::$baseUrl."/volumes/{$id}");
        if (! $response->ok()) {
            return null;
        }

        return $response->json();
    }

    protected static function buildFieldsString(?array $fields = null): string
    {
        if (! $fields) {
            $fields = self::FIELDS;
        }

        return collect($fields)
            ->map(function ($value, $key) {
                // Numeric keys → scalar values (leaf nodes)
                if (is_int($key)) {
                    return $value;
                }

                return $key.'('.self::buildFieldsString($value).')';
            })
            ->implode(',');
    }

    protected static function transform(?array $volume = null): ?array
    {
        if (! $volume || empty($volume['volumeInfo'])) {
            return null;
        }

        $book = $volume['volumeInfo'];
        $book['id'] = $volume['id'] ?? null;

        return [
            'codes' => $book['industryIdentifiers'] ?? [],
            'identifier' => $book['id'] ?? null,
            'title' => $book['title'] ?? null,
            'page_count' => $book['pageCount'] ?? null,
            'tags' => $book['categories'] ?? null,
            'publisher' => $book['publisher'] ?? null,
            'description' => $book['description'] ?? null,
            'description_clean' => ! empty($book['description']) ? strip_tags($book['description']) : null,
            'authors' => $book['authors'] ?? null,
            'published_date' => $book['publishedDate'] ?? null,
            'cover' => $book['imageLinks']['extraLarge'] ??
                      $book['imageLinks']['large'] ??
                      $book['imageLinks']['medium'] ??
                      $book['imageLinks']['thumbnail'] ??
                      $book['imageLinks']['smallThumbnail'] ?? null,
            'service' => self::ServiceName,
        ];
    }
}
