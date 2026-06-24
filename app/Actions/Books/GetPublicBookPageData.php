<?php

namespace App\Actions\Books;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ReviewResource;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPublicBookPageData
{
    use AsAction;

    /**
     * @return array{
     *     book: array<string, mixed>,
     *     average_rating: string,
     *     related: array<int, array<string, mixed>>,
     *     reviews: array<int, array<string, mixed>>,
     *     meta: array{title: string, image: string|null, description: string}
     * }
     */
    public function handle(string $path, Request $request): array
    {
        $ttl = config('books.public_page_cache.ttl_seconds');

        return Cache::remember($this->cacheKey($path), $ttl, function () use ($path): array {
            $book = Book::query()->where('path', $path)->firstOrFail();

            $book->load(['authors', 'reviews', 'ratings', 'publisher', 'tags']);

            $relatedBooks = $book->relatedBooksBySearch(4);
            $relatedBooks->each(fn (Book $related) => $related->load(['authors']));

            $reviews = $book->reviews->load('user', 'book');

            $guestRequest = Request::create('/');

            return $this->toPlainArray([
                'book' => BookResource::make($book)->resolve($guestRequest),
                'average_rating' => number_format($book->ratings->avg('value') ?? 0, 1),
                'related' => BookResource::collection($relatedBooks)->toArray($guestRequest),
                'reviews' => ReviewResource::collection($reviews)->toArray($guestRequest),
                'meta' => [
                    'title' => $book->title,
                    'image' => $book->primary_cover,
                    'description' => $book->description ?? $book->title.' by '.$book->authors->pluck('name')->implode(', '),
                ],
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function toPlainArray(array $payload): array
    {
        return json_decode(json_encode($payload, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    public function forget(Book|string $book): void
    {
        $path = $book instanceof Book ? $book->path : $book;

        Cache::forget($this->cacheKey($path));
    }

    public function cacheKey(string $path): string
    {
        $version = config('books.public_page_cache.version');

        return 'book-page:public:'.$version.':'.hash('sha256', $path);
    }
}
