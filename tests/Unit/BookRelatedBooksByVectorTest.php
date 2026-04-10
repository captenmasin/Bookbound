<?php

use App\Models\Book;
use Illuminate\Support\Collection;

test('it returns an empty collection when the book has no embedding', function () {
    $book = new Book([
        'id' => 123,
        'title' => 'Vectorless Book',
        'embedding' => null,
    ]);

    $relatedBooks = $book->relatedBooksByVector();

    expect($relatedBooks)
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});
