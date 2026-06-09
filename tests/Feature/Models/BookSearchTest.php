<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\Author;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('scout.driver', 'database');
});

it('only indexes book columns for the database scout driver', function () {
    $author = Author::factory()->create(['name' => 'Stephen King']);
    $tag = Tag::factory()->create(['name' => 'Horror']);
    $book = Book::factory()->create();
    $book->authors()->attach($author);
    $book->tags()->attach($tag);

    $searchable = $book->toSearchableArray();

    expect($searchable)->toHaveKeys(['id', 'title', 'description', 'identifier', 'path'])
        ->not->toHaveKeys(['authors', 'tags']);
});

it('finds related books by shared authors when using the database scout driver', function () {
    Cache::flush();

    $author = Author::factory()->create(['name' => 'Stephen King']);
    $book = Book::factory()->create(['title' => 'Carrie']);
    $relatedBook = Book::factory()->create(['title' => 'The Shining']);
    $unrelatedBook = Book::factory()->create(['title' => 'Pride and Prejudice']);

    $book->authors()->attach($author);
    $relatedBook->authors()->attach($author);

    $results = $book->relatedBooksBySearch(4);

    expect($results)->toHaveCount(1)
        ->and($results->first()->is($relatedBook))->toBeTrue()
        ->and($results->contains(fn (Book $result) => $result->is($unrelatedBook)))->toBeFalse();
});

it('searches books without querying non-existent relationship columns', function () {
    Book::factory()->create(['title' => 'Stephen King Collection']);

    $results = Book::search('Stephen King')->get();

    expect($results)->toHaveCount(1);
});
