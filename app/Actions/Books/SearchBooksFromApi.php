<?php

namespace App\Actions\Books;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;
use App\Transformers\BookTransformer;
use App\Jobs\ImportBooksFromApiSearch;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Contracts\BookApiServiceInterface;
use App\Jobs\ImportAdditionalBooksFromApiSearch;

class SearchBooksFromApi
{
    use AsAction;

    public function __construct(protected BookApiServiceInterface $booksApi) {}

    public function handle(
        ?string $query = null,
        ?string $author = null,
        int $maxResults = 30,
        int $page = 1): array
    {
        $results = $this->booksApi->search(query: $query, author: $author, maxResults: $maxResults, page: $page);

        $total = $results['total'] ?? 0;
        $books = collect($results['items'] ?? [])->map(fn ($book) => BookTransformer::fromIsbn($book));

        ImportBooksFromApiSearch::dispatch($books);

        if (count($books) > 0) {
            Bus::chain([
                new ImportBooksFromApiSearch($books),
                new ImportAdditionalBooksFromApiSearch(query: $query, author: $author),
            ])->onQueue('imports')->dispatch();
        }

        return [
            'total' => $total,
            'books' => $books,
        ];
    }

    public function asController(Request $request): JsonResponse
    {
        $results = $this->handle(
            $request->query('q'),
            $request->query('author'),
        );

        return response()->json($results);
    }
}
