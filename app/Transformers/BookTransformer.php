<?php

namespace App\Transformers;

use App\Models\Book;
use Illuminate\Support\Str;
use InvalidArgumentException;
use App\Services\ISBNdbService;
use App\Services\GoogleBooksService;
use App\Services\OpenLibraryService;

class BookTransformer
{
    /**
     * Transform raw book data from various providers into a standardized format
     *
     * @param  array  $data  Raw book data from the provider
     * @return array|null Standardized book data with the following structure:
     *                    - id: ?string The book ID from the provider
     *                    - identifier: string The primary ISBN identifier
     *                    - codes: array[] Array of identifier codes:
     *                    - type: string The identifier type (ISBN_13, ISBN_10)
     *                    - identifier: ?string The actual identifier value
     *                    - title: ?string Book title
     *                    - pageCount: ?int Number of pages
     *                    - tags: string[] Array of normalized subject tags
     *                    - publisher: ?array{name: string, uuid: ?string} Publisher details
     *                    - description: ?string Raw description text
     *                    - description_clean: string Cleaned description text
     *                    - authors: array{name: string, uuid: ?string}[] Array of author details
     *                    - edition: ?string Edition information
     *                    - binding: ?string Physical format/binding type
     *                    - language: ?string Language code
     *                    - published_date: ?string Publication date
     *                    - cover: ?string URL to medium cover image
     *                    - cover_large: ?string URL to large cover image
     *                    - service: string Name of the source service
     *                    - links: array{show: string} Navigation links
     */
    public static function handle(array $data): ?array
    {
        $provider = config('books.provider', '');

        return match ($provider) {
            'isbndb' => self::fromIsbn($data),
            'google' => self::fromGoogleBooks($data),
            'openlibrary' => self::fromOpenLibrary($data),
            default => throw new \InvalidArgumentException(
                "BookTransformer: unknown books.provider '{$provider}'."
            ),
        };
    }

    public static function fromIsbn(array $data): array
    {
        $identifier = $data['isbn13'] ?? $data['isbn'] ?? $data['isbn10'] ?? null;
        if (! $identifier) {
            throw new InvalidArgumentException('ISBNdb payload missing identifier.');
        }

        $links = self::linksForIdentifier($identifier);

        $subjects = self::normalizeSubjects($data['subjects'] ?? []);
        $authors = self::mapAuthors($data['authors'] ?? []);
        $desc = self::cleanDescription($data['overview'] ?? $data['synopsis'] ?? $data['description'] ?? null);

        return [
            'id' => $data['id'] ?? null,
            'identifier' => $identifier,
            'codes' => [
                ['type' => 'ISBN_13', 'identifier' => $data['isbn13'] ?? null],
                ['type' => 'ISBN_10', 'identifier' => $data['isbn'] ?? $data['isbn10'] ?? null],
            ],
            'title' => $data['title'] ?? null,
            'pageCount' => $data['pages'] ?? null,
            'tags' => $subjects,               // keep as []
            'publisher' => ! empty($data['publisher']) ? ['name' => $data['publisher'], 'uuid' => null] : null,
            'description' => $desc['raw'],
            'description_clean' => $desc['clean'],
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

    public static function fromGoogleBooks(array $data): array
    {
        // Choose identifier: prefer ISBN_13, then ISBN_10, else first code
        $fallbackIdentifier = $data['identifier'] ?? $data['isbn13'] ?? $data['isbn'] ?? $data['id'] ?? null;
        $identifier = self::pickIdentifierFromCodes($data['codes'] ?? [], $fallbackIdentifier);
        if (! $identifier) {
            throw new InvalidArgumentException('GoogleBooks payload missing codes/identifier.');
        }

        $links = self::linksForIdentifier($identifier);

        $subjects = self::normalizeSubjects($data['tags'] ?? []);
        $authors = self::mapAuthors($data['authors'] ?? []);
        $desc = self::cleanDescription($data['description_clean'] ?? $data['description'] ?? null);

        // Normalize GB cover tweaks defensively
        $cover = $data['cover'] ?? null;
        if ($cover) {
            $cover = self::replaceQueryParam($cover, 'edge', 'none'); // edge=curl -> none
        }
        $coverLarge = $cover ? self::replaceQueryParam($cover, 'zoom', '0') : null; // zoom=1 -> 0

        // Prefer passing through existing codes if present, else rebuild a minimal pair
        $codes = ! empty($data['codes'])
            ? array_values($data['codes'])
            : [
                ['type' => 'ISBN_13', 'identifier' => $data['isbn13'] ?? null],
                ['type' => 'ISBN_10', 'identifier' => $data['isbn'] ?? null],
            ];

        return [
            'id' => $data['id'] ?? null,
            'identifier' => $identifier,
            'codes' => $codes,
            'title' => $data['title'] ?? null,
            'pageCount' => $data['page_count'] ?? null,
            'tags' => $subjects,
            'publisher' => ! empty($data['publisher']) ? ['name' => $data['publisher'], 'uuid' => null] : null,
            'description' => $desc['raw'],
            'description_clean' => $desc['clean'],
            'authors' => $authors,
            'edition' => $data['edition'] ?? null,
            'binding' => $data['binding'] ?? null,
            'language' => $data['language'] ?? null,
            'published_date' => $data['date_published'] ?? null,
            'cover' => $cover,
            'cover_large' => $coverLarge,
            'service' => GoogleBooksService::ServiceName,
            'links' => $links,
        ];
    }

    public static function fromOpenLibrary(array $data): array
    {
        // --- identifiers & codes ----------------------------------------------
        $isbn13s = (array) ($data['isbn_13'] ?? []);
        $isbn10s = (array) ($data['isbn_10'] ?? []);
        $isbns = (array) ($data['isbn'] ?? []); // present in search docs

        $identifier = $isbn13s[0] ?? $isbn10s[0] ?? $isbns[0] ?? null;
        if (! $identifier) {
            throw new \InvalidArgumentException('OpenLibrary payload missing identifier (isbn_13/isbn_10/isbn).');
        }

        $codes = [
            ['type' => 'ISBN_13', 'identifier' => $isbn13s[0] ?? null],
            ['type' => 'ISBN_10', 'identifier' => $isbn10s[0] ?? null],
        ];

        // --- links -------------------------------------------------------------
        $links = self::linksForIdentifier($identifier);

        // --- subjects/tags -----------------------------------------------------
        // Edition payloads: 'subjects' can be strings or ['name'=>...]
        // Search docs: 'subject' is strings
        $rawSubjects = [];
        if (! empty($data['subjects'])) {
            $rawSubjects = collect($data['subjects'])->map(function ($s) {
                if (is_array($s) && isset($s['name'])) {
                    return (string) $s['name'];
                }

                return (string) $s;
            })->all();
        } elseif (! empty($data['subject'])) {
            $rawSubjects = (array) $data['subject'];
        }
        $subjects = self::normalizeSubjects($rawSubjects);

        // --- authors -----------------------------------------------------------
        // Prefer expanded names from the service; fallback to search doc 'author_name'
        $authorNames = (array) ($data['author_expanded'] ?? $data['author_name'] ?? []);
        $authors = self::mapAuthors($authorNames);

        // --- description -------------------------------------------------------
        $rawDesc = null;
        if (array_key_exists('description', $data)) {
            $rawDesc = is_array($data['description'])
                ? ($data['description']['value'] ?? null)
                : $data['description'];
        }
        $desc = self::cleanDescription($rawDesc);

        // --- title / pages / publisher / language / date -----------------------
        $title = $data['title'] ?? null;

        $pageCount = $data['number_of_pages']
            ?? $data['number_of_pages_median']
            ?? null;

        $publisher = null;
        if (! empty($data['publishers'])) {
            $firstPub = is_array($data['publishers'][0] ?? null)
                ? ($data['publishers'][0]['name'] ?? null)
                : ($data['publishers'][0] ?? null);
            if (! empty($firstPub)) {
                $publisher = ['name' => (string) $firstPub, 'uuid' => null];
            }
        } elseif (! empty($data['publisher'])) {
            $firstPub = (array) $data['publisher'];
            if (! empty($firstPub[0])) {
                $publisher = ['name' => (string) $firstPub[0], 'uuid' => null];
            }
        }

        // language: search docs -> ['language'=>['eng']], edition -> ['languages'=>[['key'=>'/languages/eng']]]
        $language = null;
        if (! empty($data['language']) && is_array($data['language'])) {
            $language = (string) ($data['language'][0] ?? null);
        } elseif (! empty($data['languages']) && is_array($data['languages'])) {
            $langKey = $data['languages'][0]['key'] ?? null;
            if (is_string($langKey)) {
                $language = basename($langKey); // "/languages/eng" -> "eng"
            }
        }

        $publishedDate = $data['publish_date']
            ?? (isset($data['first_publish_year']) ? (string) $data['first_publish_year'] : null);

        // --- covers ------------------------------------------------------------
        // Prefer any already-provided (e.g., augmented in service). Else build from cover_i / covers[]
        $cover = $data['cover'] ?? null;
        $coverLarge = $data['cover_large'] ?? null;

        if (! $cover || ! $coverLarge) {
            $coverId = null;
            if (! empty($data['cover_i'])) {
                $coverId = (int) $data['cover_i'];
            } elseif (! empty($data['covers']) && is_array($data['covers'])) {
                $coverId = (int) array_values($data['covers'])[0];
            }

            if ($coverId) {
                $cover = $cover ?? "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg";
                $coverLarge = $coverLarge ?? "https://covers.openlibrary.org/b/id/{$coverId}-L.jpg";
            }
        }

        // --- id field ----------------------------------------------------------
        $id = $data['id'] ?? ($data['key'] ?? null); // editions use '/books/OLxxxxxM'

        return [
            'id' => $id,
            'identifier' => $identifier,
            'codes' => $codes,
            'title' => $title,
            'pageCount' => $pageCount,
            'tags' => $subjects,
            'publisher' => $publisher,
            'description' => $desc['raw'],
            'description_clean' => $desc['clean'],
            'authors' => $authors,
            'edition' => $data['edition'] ?? null,
            'binding' => $data['physical_format'] ?? $data['binding'] ?? null,
            'language' => $language,
            'published_date' => $publishedDate,
            'cover' => $cover,
            'cover_large' => $coverLarge,
            'service' => OpenLibraryService::ServiceName,
            'links' => $links,
        ];
    }

    /* ---------- helpers ---------- */

    private static function linksForIdentifier(string $identifier): array
    {
        // If you only need an id: Book::where('identifier',$identifier)->value('id')
        $existing = Book::where('identifier', $identifier)->first();

        return $existing
            ? ['show' => route('books.show', $existing)]
            : ['show' => route('books.preview', ['identifier' => $identifier])];
    }

    private static function normalizeSubjects(array $raw): array
    {
        return collect($raw)
            ->map(fn ($s) => (string) $s)
            ->flatMap(function ($s) {
                // strip suffix after '--', un-slug, then split on common delimiters
                $base = str_replace('_', ' ', trim(Str::before($s, '--')));

                return preg_split('/\s*(?:,|&|\/)\s*/', $base) ?: [$base];
            })
            ->map(fn ($s) => Str::headline(Str::of($s)->lower()->trim()))
            ->filter() // drop empties
            ->unique()
            ->values()
            ->all();
    }

    private static function mapAuthors(array $raw): array
    {
        return collect($raw)
            ->map(fn ($a) => is_array($a) ? ($a['name'] ?? null) : $a)
            ->map(fn ($name) => [
                'name' => $name ? (string) $name : 'Unknown',
                'uuid' => null,
            ])
            ->values()
            ->all();
    }

    private static function cleanDescription(?string $desc): array
    {
        $raw = $desc;
        if ($desc === null) {
            return ['raw' => null, 'clean' => ''];
        }

        // decode entities, strip tags, collapse whitespace
        $clean = Str::of(html_entity_decode($desc))
            ->stripTags()
            ->squish()
            ->toString();

        return ['raw' => $raw, 'clean' => $clean];
    }

    private static function pickIdentifierFromCodes(array $codes, ?string $fallback = null): ?string
    {
        // Normalize to consistent shape
        $codes = collect($codes)->map(function ($c) {
            // allow either ['type'=>'ISBN_13','identifier'=>'...'] or ['type'=>'ISBN_13','value'=>'...']
            $id = $c['identifier'] ?? $c['value'] ?? null;

            return ['type' => $c['type'] ?? null, 'identifier' => $id];
        });

        $isbn13 = $codes->first(fn ($c) => strtoupper((string) $c['type']) === 'ISBN_13' && ! empty($c['identifier']));
        if ($isbn13) {
            return $isbn13['identifier'];
        }

        $isbn10 = $codes->first(fn ($c) => strtoupper((string) $c['type']) === 'ISBN_10' && ! empty($c['identifier']));
        if ($isbn10) {
            return $isbn10['identifier'];
        }

        $first = $codes->first(fn ($c) => ! empty($c['identifier']));

        return $first['identifier'] ?? $fallback;
    }

    private static function replaceQueryParam(string $url, string $key, string $value): string
    {
        // Update only if it exists; otherwise, leave URL as-is
        $parts = parse_url($url);
        if (! isset($parts['query'])) {
            return $url;
        }
        parse_str($parts['query'], $q);
        if (! array_key_exists($key, $q)) {
            return $url;
        }
        $q[$key] = $value;
        $parts['query'] = http_build_query($q);

        // Rebuild URL
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $user = $parts['user'] ?? null;
        $pass = $parts['pass'] ?? null;
        $auth = $user ? $user.($pass ? ':'.$pass : '').'@' : '';
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return "{$scheme}://{$auth}{$host}{$port}{$path}{$query}{$fragment}";
    }
}
