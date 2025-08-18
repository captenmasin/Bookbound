<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Uri;
use Illuminate\Support\Facades\Http;
use App\Contracts\BookApiServiceInterface;
use Illuminate\Http\Client\PendingRequest;

class OpenLibraryService implements BookApiServiceInterface
{
    public const ServiceName = 'OpenLibrary';

    protected static string $baseUrl = 'https://openlibrary.org';

    protected static string $coversBase = 'https://covers.openlibrary.org';

    protected static function client(): PendingRequest
    {
        static $client;

        // No API key required for OpenLibrary.
        return $client ??= Http::withHeaders([
            'Accept' => 'application/json',
        ])->timeout(10);
    }

    /**
     * Search books by free text, author, and/or subject.
     * Returns a shape: ['total' => int, 'items' => array<doc>]
     *
     * Notes:
     * - OpenLibrary search supports q, author, subject, page
     * - We request a pared-down field list to keep payloads small
     */
    public static function search(
        ?string $query = null,
        ?string $author = null,
        ?string $subject = null,
        int $maxResults = 30,
        $page = 1
    ): array {
        if (! $query && ! $author && ! $subject) {
            return [];
        }

        // Compose q: keep it simple and predictable
        $qParts = collect([$query, $author ? "author:\"$author\"" : null, $subject ? "subject:\"$subject\"" : null])
            ->filter()
            ->implode(' ');

        $params = [
            'q' => trim($qParts),
            'page' => max(1, (int) $page),
            // Ask for fewer fields to save bytes; we’ll still guard for missing ones
            'fields' => implode(',', [
                'key',
                'title',
                'author_name',
                'isbn',
                'isbn_10',
                'isbn_13',
                'number_of_pages_median',
                'subject',
                'first_publish_year',
                'language',
                'cover_i',
                'publisher',
            ]),
        ];

        $url = Uri::of(self::$baseUrl)
            ->withPath('/search.json')
            ->withQuery($params);

        try {
            $resp = self::client()
                ->retry(3, 200)
                ->get($url);
        } catch (Exception $e) {
            return [];
        }

        if (! $resp->ok()) {
            return [];
        }

        $total = (int) ($resp->json('numFound') ?? 0);
        $docs = $resp->json('docs') ?? [];

        // OL returns up to 100 docs per page; trim to $maxResults client-side
        if ($maxResults > 0) {
            $docs = array_slice($docs, 0, $maxResults);
        }

        return [
            'total' => $total,
            'items' => $docs,
        ];
    }

    /**
     * Fetch a single book record.
     * For OpenLibrary we treat $id as an ISBN to mirror your ISBNdb usage.
     */
    public static function get(string $id): ?array
    {
        return self::getFromApi($id);
    }

    public static function getByCode(string $isbn): ?array
    {
        return self::getFromApi($isbn);
    }

    /**
     * Fetch edition JSON by ISBN. Optionally expand author names.
     *
     * Returns the raw OpenLibrary edition payload augmented with:
     *  - 'author_expanded': array<string> of author names (best-effort)
     *  - 'cover_small'/'cover_medium'/'cover_large': URLs if cover IDs exist
     */
    public static function getFromApi(string $isbn): ?array
    {
        $isbn = trim($isbn);

        try {
            $editionResp = self::client()
                ->retry(3, 200)
                ->get(self::$baseUrl.'/isbn/'.urlencode($isbn).'.json');
        } catch (Exception $e) {
            return null;
        }

        if (! $editionResp->ok()) {
            return null;
        }

        $edition = $editionResp->json();
        if (! is_array($edition) || empty($edition)) {
            return null;
        }

        // --- Expand authors (best-effort; OpenLibrary gives only /authors/OLxxxW keys)
        $authorNames = [];
        $authorRefs = array_slice((array) ($edition['authors'] ?? []), 0, 5);

        foreach ($authorRefs as $ref) {
            $key = $ref['key'] ?? null; // e.g. "/authors/OL23919A"
            if (! $key || ! is_string($key)) {
                continue;
            }
            try {
                $aResp = self::client()
                    ->retry(2, 200)
                    ->get(self::$baseUrl.$key.'.json');
                if ($aResp->ok()) {
                    $name = $aResp->json('name');
                    if (is_string($name) && $name !== '') {
                        $authorNames[] = $name;
                    }
                }
            } catch (Exception $e) {
                // ignore individual failures
            }
        }

        // --- Cover URLs if present
        $coverIds = (array) ($edition['covers'] ?? []);
        if (! empty($coverIds)) {
            $first = (int) array_values($coverIds)[0];
            $edition['cover_small'] = self::coversUrl("b/id/{$first}-S.jpg");
            $edition['cover_medium'] = self::coversUrl("b/id/{$first}-M.jpg");
            $edition['cover_large'] = self::coversUrl("b/id/{$first}-L.jpg");
        }

        if (! empty($authorNames)) {
            $edition['author_expanded'] = $authorNames;
        }

        return $edition;
    }

    protected static function coversUrl(string $path): string
    {
        $path = ltrim($path, '/');

        return rtrim(self::$coversBase, '/').'/'.$path;
    }
}
