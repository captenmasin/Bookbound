<?php

namespace App\Support\Goodreads;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use App\Enums\UserBookStatus;
use InvalidArgumentException;

class GoodreadsRowNormalizer
{
    /**
     * @var string[]
     */
    public const REQUIRED_HEADERS = [
        'Title',
        'Author',
        'Additional Authors',
        'ISBN',
        'ISBN13',
        'My Rating',
        'Date Read',
        'Date Added',
        'Bookshelves',
        'Exclusive Shelf',
        'My Review',
        'Private Notes',
    ];

    public static function normalizeHeader(array $header): array
    {
        if ($header === []) {
            return [];
        }

        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];

        return array_map(fn ($value) => trim((string) $value), $header);
    }

    public static function assertRequiredHeaders(array $header): void
    {
        $missing = collect(self::REQUIRED_HEADERS)
            ->diff($header)
            ->values();

        if ($missing->isNotEmpty()) {
            throw new InvalidArgumentException('Header row is missing required Goodreads columns: '.$missing->implode(', '));
        }
    }

    /**
     * @return array{
     *     title: string,
     *     identifier: ?string,
     *     isbn10: ?string,
     *     isbn13: ?string,
     *     authors: array<int, string>,
     *     primary_author: ?string,
     *     status: string,
     *     rating: ?int,
     *     review: ?string,
     *     private_notes: ?string,
     *     user_tags: array<int, string>,
     *     added_at: ?string,
     *     read_at: ?string,
     *     raw_row: array<string, mixed>
     * }
     */
    public function normalize(array $row): array
    {
        $title = trim((string) ($row['Title'] ?? ''));

        if ($title === '') {
            throw new InvalidArgumentException('Row is missing a title.');
        }

        $isbn13 = $this->cleanIdentifier($row['ISBN13'] ?? null);
        $isbn10 = $this->cleanIdentifier($row['ISBN'] ?? null);

        $exclusiveShelf = trim((string) ($row['Exclusive Shelf'] ?? ''));
        $status = $this->mapShelfToStatus($exclusiveShelf);
        $rating = (int) trim((string) ($row['My Rating'] ?? '0'));
        $rating = $rating > 0 ? $rating : null;

        $review = $this->nullableString($row['My Review'] ?? null);
        $privateNotes = $this->nullableString($row['Private Notes'] ?? null);

        $authors = collect([
            $row['Author'] ?? null,
            ...$this->splitList($row['Additional Authors'] ?? null),
        ])->map(fn ($author) => $this->cleanAuthor((string) $author))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'title' => $title,
            'identifier' => $isbn13 ?? $isbn10,
            'isbn10' => $isbn10,
            'isbn13' => $isbn13,
            'authors' => $authors,
            'primary_author' => $authors[0] ?? null,
            'status' => $status->value,
            'rating' => $rating,
            'review' => $review,
            'private_notes' => $privateNotes,
            'user_tags' => $this->extractUserTags($row['Bookshelves'] ?? null, $exclusiveShelf),
            'added_at' => $this->parseDate($row['Date Added'] ?? null),
            'read_at' => $this->parseDate($row['Date Read'] ?? null),
            'raw_row' => $row,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function extractUserTags(?string $bookshelves, ?string $exclusiveShelf = null): array
    {
        $ignoredShelves = collect([
            'read',
            'to-read',
            'currently-reading',
            'did-not-finish',
            $exclusiveShelf,
        ])->filter()->map(fn ($shelf) => Str::lower(trim((string) $shelf)));

        return collect($this->splitList($bookshelves))
            ->map(fn ($shelf) => trim((string) $shelf))
            ->filter()
            ->reject(fn ($shelf) => $ignoredShelves->contains(Str::lower($shelf)))
            ->map(fn ($shelf) => Str::headline(str_replace('-', ' ', $shelf)))
            ->unique()
            ->values()
            ->all();
    }

    public function normalizeTitleForComparison(string $title): string
    {
        $title = Str::lower($title);
        $title = preg_replace('/\[[^\]]*\]|\([^)]*\)/', ' ', $title) ?? $title;
        $title = preg_replace('/#\d+/', ' ', $title) ?? $title;
        $title = preg_replace('/[^a-z0-9\s]/', ' ', $title) ?? $title;

        return trim(preg_replace('/\s+/', ' ', $title) ?? $title);
    }

    /**
     * @param  array<int, string>  $authors
     * @return array<int, string>
     */
    public function surnames(array $authors): array
    {
        return collect($authors)
            ->map(function ($author) {
                $author = trim(preg_replace('/[^\pL\pN\s-]/u', '', $author) ?? $author);
                $parts = preg_split('/\s+/', $author) ?: [];

                return $parts === [] ? null : Str::lower((string) end($parts));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function cleanIdentifier(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '=""') {
            return null;
        }

        if (preg_match('/^="(.+)"$/', $value, $matches) === 1) {
            $value = $matches[1];
        }

        $value = preg_replace('/[^0-9Xx]/', '', $value) ?? '';

        return $value !== '' ? Str::upper($value) : null;
    }

    protected function cleanAuthor(string $author): ?string
    {
        $author = trim(preg_replace('/\s+/', ' ', $author) ?? $author);
        $author = rtrim($author, '.');

        return $author !== '' ? $author : null;
    }

    /**
     * @return array<int, string>
     */
    protected function splitList(mixed $value): array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    protected function parseDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return CarbonImmutable::createFromFormat('Y/m/d', $value)
            ->startOfDay()
            ->toDateTimeString();
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    protected function mapShelfToStatus(?string $exclusiveShelf): UserBookStatus
    {
        return match (Str::lower(trim((string) $exclusiveShelf))) {
            'currently-reading' => UserBookStatus::Reading,
            'read' => UserBookStatus::Read,
            'did-not-finish' => UserBookStatus::Dropped,
            'to-read' => UserBookStatus::PlanToRead,
            default => UserBookStatus::PlanToRead,
        };
    }
}
