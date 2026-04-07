<?php

use App\Enums\UserBookStatus;
use App\Support\Goodreads\GoodreadsRowNormalizer;

test('it normalizes a goodreads row with wrapped isbn values and merged authors', function () {
    $normalizer = app(GoodreadsRowNormalizer::class);

    $row = $normalizer->normalize([
        'Title' => 'Jurassic Park (Jurassic Park, #1)',
        'Author' => 'Michael Crichton',
        'Additional Authors' => 'James Patterson, Michael Crichton',
        'ISBN' => '="0345538986"',
        'ISBN13' => '="9780345538987"',
        'My Rating' => '5',
        'Date Read' => '2026/03/22',
        'Date Added' => '2025/12/29',
        'Bookshelves' => 'kindle, favourites',
        'Exclusive Shelf' => 'read',
        'My Review' => 'Still works.',
        'Private Notes' => 'Re-read this summer.',
    ]);

    expect($row['identifier'])->toBe('9780345538987')
        ->and($row['isbn10'])->toBe('0345538986')
        ->and($row['authors'])->toBe(['Michael Crichton', 'James Patterson'])
        ->and($row['status'])->toBe(UserBookStatus::Read->value)
        ->and($row['rating'])->toBe(5)
        ->and($row['user_tags'])->toBe(['Kindle', 'Favourites'])
        ->and($row['added_at'])->toBe('2025-12-29 00:00:00')
        ->and($row['read_at'])->toBe('2026-03-22 00:00:00');
});

test('it validates required goodreads headers', function () {
    GoodreadsRowNormalizer::assertRequiredHeaders(['Title', 'Author']);
})->throws(InvalidArgumentException::class, 'Header row is missing required Goodreads columns');

test('it normalizes titles and extracts overlapping surnames for conservative matching', function () {
    $normalizer = app(GoodreadsRowNormalizer::class);

    expect($normalizer->normalizeTitleForComparison('Red Dragon (Hannibal Lecter, #1)'))
        ->toBe('red dragon')
        ->and($normalizer->surnames(['Thomas Harris', 'Neil Gaiman']))
        ->toBe(['harris', 'gaiman']);
});
