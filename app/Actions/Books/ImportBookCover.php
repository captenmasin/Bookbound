<?php

namespace App\Actions\Books;

use App\Models\Book;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportBookCover
{
    use AsAction;

    public function handle(Book $book, ?string $coverUrl = null): void
    {
        try {
            $book->primaryCover()->addMediaFromUrl($coverUrl)
                ->toMediaCollection('image');

            $book->updateColour();
        } catch (\Exception $e) {
            \Log::error('Failed to fetch cover image for book: '.$book->identifier, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
