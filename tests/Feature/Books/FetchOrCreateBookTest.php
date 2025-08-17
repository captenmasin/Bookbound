<?php

use App\Models\Book;
use Illuminate\Http\Request;
use App\Actions\Books\FetchOrCreateBook;
use App\Actions\Books\ImportBookFromData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('handle() returns an existing book with relations', function () {
    $book = Book::factory()
        ->hasAuthors(2)
        ->forPublisher()
        ->create(['identifier' => 'abc-123']);

    $found = app(FetchOrCreateBook::class)->handle('abc-123');

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($book->id)
        ->and($found->relationLoaded('authors'))->toBeTrue()
        ->and($found->relationLoaded('publisher'))->toBeTrue();
});

test('handle() imports a book when not found', function () {
    $fakeBook = Book::factory()->make(['identifier' => 'import-123']);
    ImportBookFromData::mock()
        ->shouldReceive('handle')
        ->once()
        ->with('import-123')
        ->andReturn($fakeBook);

    $result = app(FetchOrCreateBook::class)->handle('import-123');

    expect($result->identifier)->toBe('import-123');
});

test('asController returns a JSON response with BookResource', function () {
    $book = Book::factory()->create(['identifier' => 'json-456']);

    $request = Request::create('/books/json-456', 'GET');
    $response = app(FetchOrCreateBook::class)->asController($request, 'json-456');

    expect($response->status())->toBe(200);

    $json = $response->getData(true);

    expect($json)
        ->toHaveKey('book')
        ->and($json['book']['id'])->toBe($book->id)
        ->and($json['book']['identifier'])->toBe('json-456');
});
