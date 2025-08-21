<?php

use App\Models\Book;
use App\Actions\Books\ImportBookCover;

describe('ImportBookCover', function () {
    it('successfully imports cover from url and updates color', function () {
        $book = Book::factory()->create();
        $coverUrl = 'https://placehold.co/600x400/000000/FFFFFF/png';

        $action = new ImportBookCover;
        $action->handle($book, $coverUrl);

        $primaryCover = $book->primaryCover();
        expect($primaryCover->hasMedia('image'))->toBeTrue();
    });

    it('handles failed cover import gracefully', function () {
        $book = Book::factory()->create();
        $invalidUrl = 'https://invalid-url-that-does-not-exist.com/cover.jpg';

        $action = new ImportBookCover;

        // Should not throw exception
        expect(function () use ($action, $book, $invalidUrl) {
            $action->handle($book, $invalidUrl);
        })->not->toThrow(Exception::class);

        $primaryCover = $book->primaryCover();
        expect($primaryCover->hasMedia('image'))->toBeFalse();
    });

    it('can import cover when null url is provided', function () {
        $book = Book::factory()->create();

        $action = new ImportBookCover;

        expect(function () use ($action, $book) {
            $action->handle($book, null);
        })->not->toThrow(Exception::class);
    });
});
