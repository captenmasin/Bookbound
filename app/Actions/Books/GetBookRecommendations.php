<?php

namespace App\Actions\Books;

use Throwable;
use App\Models\Book;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Ai\Agents\BookRecommendationAgent;

class GetBookRecommendations
{
    use AsAction;

    protected const int CACHE_VERSION = 3;

    protected const int SKIP_CACHE_VERSION = 2;

    protected const float LOCAL_MATCH_THRESHOLD = 0.72;

    protected const float API_MATCH_THRESHOLD = 0.80;

    /**
     * @return list<array{book: Book, reason: string}>
     */
    public function handle(User $user, int $limit = 5): array
    {
        $limit = min(max($limit, 1), $limit);

        $libraryBooks = $this->getLibraryBooks($user);

        if ($libraryBooks->count() < 2) {
            return [];
        }

        $cacheKey = $this->cacheKey($user, $libraryBooks, $limit);
        $cachedRecommendations = Cache::get($cacheKey);

        if (is_array($cachedRecommendations)) {
            return $cachedRecommendations;
        }

        try {
            $aiRecommendations = BookRecommendationAgent::make(
                user: $user,
                libraryBooks: $libraryBooks,
            )->recommend(5);
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }

        $resolvedRecommendations = $this->resolveRecommendations($aiRecommendations, $libraryBooks, $limit);

        Cache::put($cacheKey, $resolvedRecommendations, now()->addHours(12));

        return $resolvedRecommendations;
    }

    /**
     * @return Collection<int, Book>
     */
    protected function getLibraryBooks(User $user): Collection
    {
        return $user->books()
            ->with(['authors', 'categories', 'tags'])
            ->withPivot(['status', 'created_at', 'updated_at'])
            ->get();
    }

    /**
     * @param  list<array{title: string, author: string, published_year: ?int, reason: string}>  $recommendations
     * @return list<array{book: Book, reason: string}>
     */
    protected function resolveRecommendations(array $recommendations, Collection $libraryBooks, int $limit): array
    {
        $excludedBookIds = $libraryBooks->pluck('id')->all();
        $resolvedBookIds = [];

        return collect($recommendations)
            ->map(function (array $recommendation) use ($excludedBookIds, &$resolvedBookIds): ?array {
                $result = $this->resolveRecommendation(
                    recommendation: $recommendation,
                    excludedBookIds: array_values(array_unique([...$excludedBookIds, ...$resolvedBookIds])),
                );

                if ($result) {
                    $resolvedBookIds[] = $result['book']->id;
                }

                return $result;
            })
            ->filter()
            ->unique(fn (array $entry): int => $entry['book']->id)
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @param  array{title: string, author: string, published_year: ?int, reason: string}  $recommendation
     * @param  array<int>  $excludedBookIds
     * @return array{book: Book, reason: string}|null
     */
    protected function resolveRecommendation(array $recommendation, array $excludedBookIds = []): ?array
    {
        $skipCacheKey = $this->skipCacheKey($recommendation);

        if (Cache::get($skipCacheKey) === true) {
            return null;
        }

        $book = $this->findLocalMatch($recommendation, $excludedBookIds);

        if (! $book) {
            $book = $this->findOrImportApiMatch($recommendation, $excludedBookIds);
        }

        if (! $book) {
            Cache::put($skipCacheKey, true, now()->addMinutes(30));

            return null;
        }

        return [
            'book' => $book->loadMissing(['authors', 'categories', 'tags', 'publisher']),
            'reason' => $recommendation['reason'],
        ];
    }

    /**
     * @param  array{title: string, author: string, published_year: ?int, reason: string}  $recommendation
     * @param  array<int>  $excludedBookIds
     */
    protected function findLocalMatch(array $recommendation, array $excludedBookIds): ?Book
    {
        $titleTokens = collect(preg_split('/\s+/', Str::lower($recommendation['title'])) ?: [])
            ->filter(fn (string $token): bool => mb_strlen($token) >= 3)
            ->take(5)
            ->values();

        $authorTokens = collect(preg_split('/\s+/', Str::lower($recommendation['author'])) ?: [])
            ->filter(fn (string $token): bool => mb_strlen($token) >= 3)
            ->take(4)
            ->values();

        $candidates = Book::query()
            ->with(['authors', 'categories', 'tags', 'publisher'])
            ->whereNotIn('id', $excludedBookIds)
            ->where(function ($query) use ($recommendation, $titleTokens, $authorTokens): void {
                $query->whereRaw('LOWER(title) LIKE ?', ['%'.Str::lower($recommendation['title']).'%']);

                foreach ($titleTokens as $token) {
                    $query->orWhereRaw('LOWER(title) LIKE ?', ['%'.$token.'%']);
                }

                $query->orWhereHas('authors', function ($authorQuery) use ($recommendation, $authorTokens): void {
                    $authorQuery->whereRaw('LOWER(name) LIKE ?', ['%'.Str::lower($recommendation['author']).'%']);

                    foreach ($authorTokens as $token) {
                        $authorQuery->orWhereRaw('LOWER(name) LIKE ?', ['%'.$token.'%']);
                    }
                });
            })
            ->limit(25)
            ->get();

        $bestMatch = $candidates
            ->map(fn (Book $book): array => [
                'book' => $book,
                'score' => $this->scoreBookMatch(
                    title: $recommendation['title'],
                    author: $recommendation['author'],
                    publishedYear: $recommendation['published_year'],
                    bookTitle: $book->title,
                    authorNames: $book->authors->pluck('name')->all(),
                    bookPublishedDate: $book->published_date,
                ),
            ])
            ->sortByDesc('score')
            ->first();

        if (! $bestMatch || $bestMatch['score'] < self::LOCAL_MATCH_THRESHOLD) {
            return null;
        }

        return $bestMatch['book'];
    }

    /**
     * @param  array{title: string, author: string, published_year: ?int, reason: string}  $recommendation
     * @param  array<int>  $excludedBookIds
     */
    protected function findOrImportApiMatch(array $recommendation, array $excludedBookIds): ?Book
    {
        $results = SearchBooksFromApi::run(
            query: $this->buildApiSearchQuery($recommendation),
            author: null,
            maxResults: 10,
            page: 1,
        );

        if (empty($results['books'])) {
            $results = SearchBooksFromApi::run(
                query: $recommendation['title'],
                author: $recommendation['author'],
                maxResults: 10,
                page: 1,
            );
        }

        $bestResult = collect($results['books'] ?? [])
            ->map(function (array $bookData) use ($recommendation): array {
                return [
                    'data' => $bookData,
                    'score' => $this->scoreBookMatch(
                        title: $recommendation['title'],
                        author: $recommendation['author'],
                        publishedYear: $recommendation['published_year'],
                        bookTitle: $bookData['title'] ?? '',
                        authorNames: collect($bookData['authors'] ?? [])->pluck('name')->all(),
                        bookPublishedDate: $bookData['published_date'] ?? null,
                    ),
                ];
            })
            ->sortByDesc('score')
            ->first();

        if (! $bestResult || $bestResult['score'] < self::API_MATCH_THRESHOLD) {
            return null;
        }

        $book = ImportBookFromData::run($bestResult['data']);

        if (in_array($book->id, $excludedBookIds, true)) {
            return null;
        }

        return $book;
    }

    /**
     * @param  list<string>  $authorNames
     */
    protected function scoreBookMatch(
        string $title,
        string $author,
        ?int $publishedYear,
        string $bookTitle,
        array $authorNames,
        mixed $bookPublishedDate,
    ): float {
        $titleScore = $this->bestTitleScore($title, $bookTitle);
        $authorScore = collect($authorNames)
            ->map(fn (string $authorName): float => $this->similarityScore($author, $authorName))
            ->max() ?? 0.0;

        $yearScore = 0.0;
        $bookYear = $this->extractYear($bookPublishedDate);

        if ($publishedYear && $bookYear) {
            $distance = abs($publishedYear - $bookYear);
            $yearScore = match (true) {
                $distance === 0 => 1.0,
                $distance <= 1 => 0.8,
                $distance <= 3 => 0.5,
                default => 0.0,
            };
        }

        $titleScore = max(0.0, $titleScore - $this->titleNoisePenalty($title, $bookTitle));

        return ($titleScore * 0.65) + ($authorScore * 0.30) + ($yearScore * 0.05);
    }

    protected function bestTitleScore(string $title, string $bookTitle): float
    {
        $titleVariants = $this->titleVariants($title);
        $bookTitleVariants = $this->titleVariants($bookTitle);

        return collect($titleVariants)
            ->flatMap(fn (string $left): array => collect($bookTitleVariants)
                ->map(fn (string $right): float => $this->similarityScore($left, $right))
                ->all())
            ->max() ?? 0.0;
    }

    protected function similarityScore(string $left, string $right): float
    {
        $left = $this->normalizeMatchString($left);
        $right = $this->normalizeMatchString($right);

        if ($left === '' || $right === '') {
            return 0.0;
        }

        similar_text($left, $right, $similarityPercent);

        return $similarityPercent / 100;
    }

    protected function normalizeMatchString(string $value): string
    {
        return Str::of(Str::lower($value))
            ->replaceMatches('/[^a-z0-9\s]/', ' ')
            ->squish()
            ->value();
    }

    protected function extractYear(mixed $value): ?int
    {
        if ($value instanceof DateTimeInterface) {
            return (int) $value->format('Y');
        }

        if (is_numeric($value) && strlen((string) $value) === 4) {
            return (int) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        if (preg_match('/\b(1[0-9]{3}|20[0-9]{2}|2100)\b/', $value, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * @param  array{title: string, author: string, published_year: ?int, reason: string}  $recommendation
     */
    protected function skipCacheKey(array $recommendation): string
    {
        return 'book_recommendation_skip_v'.self::SKIP_CACHE_VERSION.'_'.sha1(
            Str::lower($recommendation['title'].'|'.$recommendation['author'].'|'.($recommendation['published_year'] ?? ''))
        );
    }

    /**
     * @param  array{title: string, author: string, published_year: ?int, reason: string}  $recommendation
     */
    protected function buildApiSearchQuery(array $recommendation): string
    {
        return Str::of($recommendation['title'].' '.$recommendation['author'])
            ->squish()
            ->value();
    }

    /**
     * @return list<string>
     */
    protected function titleVariants(string $value): array
    {
        $variants = collect([
            $value,
            $this->primaryTitle($value),
        ])->map(fn (string $variant): string => $this->normalizeMatchString($variant))
            ->filter()
            ->unique()
            ->values();

        return $variants->all();
    }

    protected function primaryTitle(string $value): string
    {
        return Str::of($value)
            ->replaceMatches('/\s*\([^)]*\)/', '')
            ->before(':')
            ->before(' - ')
            ->before(' — ')
            ->squish()
            ->value();
    }

    protected function titleNoisePenalty(string $title, string $bookTitle): float
    {
        $normalizedTitle = $this->normalizeMatchString($title);
        $normalizedBookTitle = $this->normalizeMatchString($bookTitle);

        if ($normalizedTitle === '' || $normalizedBookTitle === '') {
            return 0.0;
        }

        $titleTokenCount = count(preg_split('/\s+/', $normalizedTitle) ?: []);
        $bookTitleTokenCount = count(preg_split('/\s+/', $normalizedBookTitle) ?: []);
        $extraTokenCount = max(0, $bookTitleTokenCount - $titleTokenCount);

        if ($extraTokenCount < 3 || Str::startsWith($normalizedBookTitle, $normalizedTitle)) {
            return 0.0;
        }

        return min(0.35, $extraTokenCount * 0.05);
    }

    protected function cacheKey(User $user, Collection $libraryBooks, int $limit): string
    {
        $fingerprint = $libraryBooks
            ->sortBy('id')
            ->map(function (Book $book): string {
                return implode(':', [
                    $book->id,
                    $book->pivot?->status ?? 'unknown',
                    $book->pivot?->updated_at?->timestamp ?? 0,
                    $book->updated_at?->timestamp ?? 0,
                ]);
            })
            ->implode('|');

        return 'book_recommendations_v'.self::CACHE_VERSION.'_'.$user->id.'_'.sha1($fingerprint).'_'.$limit;
    }
}
