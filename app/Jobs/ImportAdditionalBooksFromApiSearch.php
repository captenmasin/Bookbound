<?php

namespace App\Jobs;

use App\Transformers\BookTransformer;
use App\Contracts\BookApiServiceInterface;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportAdditionalBooksFromApiSearch implements ShouldQueue
{
    use Queueable;

    public BookApiServiceInterface $booksApi;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $query = null,
        public ?string $author = null,
        public ?string $subject = null,
    ) {
        $this->booksApi = app(BookApiServiceInterface::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $results = $this->booksApi->search(query: $this->query, author: $this->author, subject: $this->subject, maxResults: 100);

        $books = collect($results['items'] ?? [])->map(fn ($book) => BookTransformer::fromIsbn($book));

        ImportBooksFromApiSearch::dispatch($books);
    }
}
