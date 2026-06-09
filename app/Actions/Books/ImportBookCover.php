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
        } catch (\Exception $e) {
            \Log::error('Failed to fetch cover image for book: '.$book->identifier, [
                'error' => $e->getMessage(),
            ]);

            return;
        }

        try {
            $book->updateColour();
        } catch (\Exception $e) {
            \Log::warning('Failed to extract cover colour for book: '.$book->identifier, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
