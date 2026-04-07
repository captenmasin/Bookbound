<?php

namespace App\Ai\Agents;

use Stringable;
use App\Models\Book;
use App\Models\User;
use RuntimeException;
use Laravel\Ai\Promptable;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Messages\Message;
use Illuminate\Support\Collection;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Responses\StructuredAgentResponse;

class BookRecommendationAgent implements Agent, Conversational, HasStructuredOutput
{
    use Promptable;

    /**
     * @param  Collection<int, Book>  $libraryBooks
     */
    public function __construct(
        public User $user,
        public Collection $libraryBooks,
    ) {
        $this->libraryBooks->each(fn (Book $book) => $book->loadMissing(['authors', 'categories', 'tags']));
    }

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You recommend concrete books for a reader based on their library.

Rules:
- Return up to five recommendations.
- Recommend specific books with a title and one primary author.
- Do not recommend books the reader already owns.
- Prefer books that align with the reader's repeated authors, tags, categories, tone, and recent reading habits.
- Reasons must be concise, specific, and reader-facing.
- Mention concrete overlaps when possible, such as genre, themes, authors, era, or subject.
- Do not invent plot details beyond broadly known information.
- If you are unsure of a publication year, omit it.
PROMPT;
    }

    /**
     * @return list<array{title: string, author: string, published_year: ?int, reason: string}>
     */
    public function recommend(int $limit = 5): array
    {
        $limit = min(max($limit, 1), 5);

        $response = $this->prompt(
            "Recommend up to {$limit} books for this reader as concrete title and author suggestions."
        );

        if (! $response instanceof StructuredAgentResponse) {
            throw new RuntimeException('BookRecommendationAgent expected a structured response.');
        }

        return collect($response->toArray()['recommendations'] ?? [])
            ->map(function (mixed $recommendation): ?array {
                $title = Str::of((string) data_get($recommendation, 'title'))
                    ->squish()
                    ->limit(140, '')
                    ->value();
                $author = Str::of((string) data_get($recommendation, 'author'))
                    ->squish()
                    ->limit(100, '')
                    ->value();
                $reason = Str::of((string) data_get($recommendation, 'reason'))
                    ->squish()
                    ->limit(180, '')
                    ->value();
                $publishedYear = data_get($recommendation, 'published_year');

                if (blank($title) || blank($author) || blank($reason)) {
                    return null;
                }

                if (! is_numeric($publishedYear)) {
                    $publishedYear = null;
                } else {
                    $publishedYear = (int) $publishedYear;
                }

                return [
                    'title' => $title,
                    'author' => $author,
                    'published_year' => $publishedYear,
                    'reason' => $reason,
                ];
            })
            ->filter()
            ->unique(fn (array $recommendation): string => Str::lower($recommendation['title'].'|'.$recommendation['author']))
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [
            new UserMessage($this->readerProfileContext()),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'recommendations' => $schema
                ->array()
                ->description('Up to five concrete book recommendations for the reader.')
                ->items(
                    $schema->object(
                        properties: [
                            'title' => $schema
                                ->string()
                                ->description('The book title.')
                                ->required(),
                            'author' => $schema
                                ->string()
                                ->description('One primary author name for the recommended book.')
                                ->required(),
                            'published_year' => $schema
                                ->integer()
                                ->nullable()
                                ->description('The publication year when known.')
                                ->required(),
                            'reason' => $schema
                                ->string()
                                ->description('A concise recommendation reason grounded in the reader profile.')
                                ->required(),
                        ]
                    )->withoutAdditionalProperties()
                )
                ->min(0)
                ->max(5)
                ->required(),
        ];
    }

    protected function readerProfileContext(): string
    {
        $statusCounts = $this->libraryBooks
            ->map(fn (Book $book): ?string => $book->pivot?->status)
            ->filter()
            ->countBy()
            ->map(fn (int $count, string $status): string => "{$status} ({$count})")
            ->implode(', ');

        $topAuthors = $this->libraryBooks
            ->flatMap(fn (Book $book) => $book->authors->pluck('name'))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(6)
            ->map(fn (int $count, string $author): string => "{$author} ({$count})")
            ->implode(', ');

        $topTags = $this->libraryBooks
            ->flatMap(fn (Book $book) => $book->tags->pluck('name'))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(8)
            ->map(fn (int $count, string $tag): string => "{$tag} ({$count})")
            ->implode(', ');

        $topCategories = $this->libraryBooks
            ->flatMap(fn (Book $book) => $book->categories->pluck('name'))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(8)
            ->map(fn (int $count, string $category): string => "{$category} ({$count})")
            ->implode(', ');

        $recentBooks = $this->libraryBooks
            ->sortByDesc(fn (Book $book) => $book->pivot?->created_at?->timestamp ?? 0)
            ->take(8)
            ->map(function (Book $book): string {
                $status = $book->pivot?->status ?? 'Unknown';
                $authors = $book->authors->pluck('name')->take(2)->implode(', ');

                return "{$book->title} by ".($authors ?: 'Unknown')." [{$status}]";
            })
            ->implode('; ');

        $ownedTitles = $this->libraryBooks
            ->map(fn (Book $book): string => $book->title)
            ->filter()
            ->take(80)
            ->implode('; ');

        return implode("\n", [
            'Reader profile:',
            'Name: '.($this->user->name ?: 'Unknown'),
            'Books in library: '.$this->libraryBooks->count(),
            'Status mix: '.($statusCounts ?: 'Unknown'),
            'Top authors: '.($topAuthors ?: 'None'),
            'Top tags: '.($topTags ?: 'None'),
            'Top categories: '.($topCategories ?: 'None'),
            'Recent library books: '.($recentBooks ?: 'None'),
            'Already owned titles: '.($ownedTitles ?: 'None'),
        ]);
    }
}
