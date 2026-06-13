<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Support\Facades\Queue;

test('database Scout searchable data only contains real book columns', function () {
    config(['scout.driver' => 'database']);

    $book = Book::factory()->make([
        'title' => 'The Searchable Book',
        'description' => 'A searchable description.',
        'identifier' => 'searchable-book',
        'path' => 'the-searchable-book',
    ]);

    expect($book->toSearchableArray())
        ->toHaveKeys(['id', 'title', 'description', 'identifier', 'path'])
        ->not->toHaveKeys(['authors', 'tags', 'categories', 'embedding']);
});

test('third party Scout searchable data includes relationship names without embedding vectors', function () {
    config(['scout.driver' => 'meilisearch']);
    Queue::fake();

    $book = Book::withoutSyncingToSearch(fn () => Book::factory()->create());
    $author = Author::factory()->create(['name' => 'Octavia Butler']);
    $tag = Tag::factory()->create(['name' => 'science-fiction']);
    $category = Category::query()->create(['name' => 'Speculative Fiction']);

    $book->authors()->attach($author);
    $book->tags()->attach($tag);
    $book->categories()->attach($category);

    expect($book->toSearchableArray())
        ->toMatchArray([
            'authors' => ['Octavia Butler'],
            'tags' => ['science-fiction'],
            'categories' => ['Speculative Fiction'],
        ])
        ->not->toHaveKey('embedding');
});
