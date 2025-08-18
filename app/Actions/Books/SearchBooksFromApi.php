<?php

namespace App\Actions\Books;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;
use App\Transformers\BookTransformer;
use Illuminate\Support\Facades\Cache;
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
        ?string $subject = null,
        int $maxResults = 30,
        int $page = 1): array
    {
        $hash = md5("$query|$author|$subject|$maxResults|$page");
        $cacheKey = "books:search:$hash";

        $data = Cache::remember($cacheKey, now()->addHour(), function () use ($query, $author, $subject, $maxResults, $page) {
            $results = $this->booksApi->search(query: $query, author: $author, subject: $subject, maxResults: $maxResults, page: $page);

            $total = $results['total'] ?? 0;
            $books = collect($results['items'] ?? [])->map(fn ($book) => BookTransformer::handle($book));

            // Store in cache for 5 minutes for faster ImportBookFromData
            $books->each(function ($data) {
                Cache::remember('book:'.$data['identifier'], now()->addMinutes(5), function () use ($data) {
                    return $data;
                });
            });

            if (count($books) > 0) {
                ImportBooksFromApiSearch::dispatch($books);

                Bus::chain([
                    new ImportBooksFromApiSearch($books),
                    new ImportAdditionalBooksFromApiSearch(query: $query, author: $author, subject: $subject),
                ])->onQueue('imports')->dispatch();
            }

            return [
                'total' => $total,
                'books' => $books,
            ];
        });

        if (! $data || $data['total'] === 0) {
            Cache::forget($cacheKey);
        }

        return $data;
    }

    public function asController(Request $request): JsonResponse
    {
        $results = $this->handle(
            $request->query('q'),
            $request->query('author'),
            $request->query('subject'),
        );

        return response()->json($results);
    }
}
