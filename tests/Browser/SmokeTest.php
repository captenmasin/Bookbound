<?php

use App\Models\Book;

it('loads basic pages with no 500 errors ', function ($route) {
    if ($route === 'books/test-book-1') {
        Book::factory()->create([
            'title' => 'Test Book',
            'identifier' => '1',
        ]);
    }

    $response = $this->get($route);

    expect($response->status())->not->toBe(500);
})->with('routes');

it('loads basic pages with no smoke', function ($route) {
    if ($route === 'books/test-book-1') {
        Book::factory()->create([
            'title' => 'Test Book',
            'identifier' => '1',
        ]);
    }

    $page = visit($route);

    $page->assertNoSmoke()
        ->assertNoConsoleLogs()
        ->assertNoJavaScriptErrors();

})->with('routes');
