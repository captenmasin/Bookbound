<?php

namespace App\Actions\Books;

use App\Contracts\BookApiServiceInterface;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchOrCreateBook
{
    use AsAction;

    public function __construct(protected BookApiServiceInterface $booksApi) {}

    public function handle(string $identifier)
    {
        $book = Book::where('identifier', $identifier)
            ->with(['covers', 'authors', 'publisher', 'tags'])
            ->firstOr(fn () => null);

        return $book ?: ImportBookFromData::run($identifier);
    }

    public function asController(Request $request, string $identifier): JsonResponse
    {
        $book = $this->handle($identifier);

        return response()->json([
            'book' => new BookResource($book),
        ]);
    }
}
