<?php

use App\Models\Book;

use function Pest\Laravel\get;

it('loads basic pages with no 500 errors ', function ($route) {
    if ($route === '/books/test-book-1') {
        Book::factory()->create([
            'path' => 'test-book-1',
            'title' => 'Test Book',
            'identifier' => '1',
        ]);
    }

    $response = get($route);

    expect($response->status())->toBe(200);
})->with('routes');

it('loads basic pages with no smoke', function ($route) {
    if ($route === '/books/test-book-1') {
        Book::factory()->create([
            'path' => 'test-book-1',
            'title' => 'Test Book',
            'identifier' => '1',
        ]);
    }

    visit($route)->assertNoSmoke();
})->with('routes');
