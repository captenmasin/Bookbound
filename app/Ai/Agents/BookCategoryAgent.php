<?php

namespace App\Ai\Agents;

use Stringable;
use App\Models\Book;
use RuntimeException;
use Laravel\Ai\Promptable;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Responses\StructuredAgentResponse;

class BookCategoryAgent implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    public function __construct(public Book $book)
    {
        $this->book->loadMissing(['authors', 'publisher', 'tags']);
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You categorize books into one or two concise, reader-facing bookshelf categories.

Rules:
- Return one or two categories only.
- Categories should be short, clear, and useful in a bookstore or personal library.
- Prefer categories that combine genre, period, movement, or subject when helpful, such as "Philosophy & Ethics", "Classical Literature", "Modern History", "Political Theory", or "Biography & Memoir".
- Use Title Case.
- Do not return author names, titles, keywords, or overly niche topics.
- Use the supplied title, description, authors, publisher, publication date, and existing tags to infer the best fit.
- If there is one obvious dominant category, return a single category.
PROMPT;
    }

    /**
     * Categorize the configured book into one or two bookshelf categories.
     *
     * @return list<string>
     */
    public function categorize(): array
    {
        $response = $this->prompt(
            'Assign one or two concise, reader-facing bookshelf categories for this book.'
        );

        if (! $response instanceof StructuredAgentResponse) {
            throw new RuntimeException('BookCategoryAgent expected a structured response.');
        }

        return collect($response->toArray()['categories'] ?? [])
            ->filter(fn (mixed $category): bool => is_string($category) && filled(trim($category)))
            ->map(fn (string $category): string => Str::of($category)->squish()->limit(60, '')->value())
            ->unique()
            ->take(2)
            ->values()
            ->all();
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [
            new UserMessage($this->bookContext()),
        ];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'categories' => $schema
                ->array()
                ->description('One or two concise bookshelf categories for the book.')
                ->items(
                    $schema
                        ->string()
                        ->description('A concise Title Case category like "Philosophy & Ethics".')
                )
                ->min(1)
                ->max(2)
                ->required(),
        ];
    }

    protected function bookContext(): string
    {
        $authors = $this->book->authors
            ->pluck('name')
            ->filter()
            ->implode(', ');

        $tags = $this->book->tags
            ->pluck('name')
            ->filter()
            ->implode(', ');

        $description = Str::of((string) $this->book->description)
            ->stripTags()
            ->squish()
            ->limit(4000)
            ->value();

        return implode("\n", [
            'Book details:',
            'Title: '.($this->book->title ?: 'Unknown'),
            'Authors: '.($authors ?: 'Unknown'),
            'Publisher: '.($this->book->publisher?->name ?: 'Unknown'),
            'Published: '.($this->book->published_date ?: 'Unknown'),
            'Page count: '.($this->book->page_count ?: 'Unknown'),
            'Edition: '.($this->book->edition ?: 'Unknown'),
            'Binding: '.($this->book->binding ?: 'Unknown'),
            'Language: '.($this->book->language ?: 'Unknown'),
            'Existing tags: '.($tags ?: 'None'),
            'Description: '.($description ?: 'None'),
        ]);
    }
}
