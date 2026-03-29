<?php

use App\Models\Tag;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use Illuminate\Support\Collection;
use App\Ai\Agents\BookCategoryAgent;
use Laravel\Ai\Messages\UserMessage;

uses(TestCase::class);

test('it categorizes a book using its metadata as context', function () {
    $book = Book::factory()->make([
        'title' => 'Meditations',
        'description' => '<p>A classic work on Stoic philosophy, virtue, discipline, and ethical self-examination.</p>',
        'published_date' => '0180',
        'page_count' => 304,
        'edition' => 'Penguin Classics',
        'binding' => 'Paperback',
        'language' => 'en',
    ]);

    $book->setRelation('authors', new Collection([
        new Author(['name' => 'Marcus Aurelius']),
    ]));

    $book->setRelation('tags', new Collection([
        new Tag(['name' => 'Stoicism']),
        new Tag(['name' => 'Ancient Rome']),
    ]));

    $book->setRelation('publisher', new Publisher([
        'name' => 'Penguin Classics',
    ]));

    BookCategoryAgent::fake([
        ['categories' => ['Philosophy & Ethics', 'Classical Literature']],
    ]);

    $categories = BookCategoryAgent::make(book: $book)->categorize();

    expect($categories)->toBe([
        'Philosophy & Ethics',
        'Classical Literature',
    ]);

    BookCategoryAgent::assertPrompted(function ($prompt) {
        expect($prompt->prompt)->toBe(
            'Assign one or two concise, reader-facing bookshelf categories for this book.'
        );

        $message = collect($prompt->agent->messages())->sole();

        expect($message)->toBeInstanceOf(UserMessage::class);
        expect($message->content)->toContain('Title: Meditations');
        expect($message->content)->toContain('Authors: Marcus Aurelius');
        expect($message->content)->toContain('Publisher: Penguin Classics');
        expect($message->content)->toContain('Existing tags: Stoicism, Ancient Rome');
        expect($message->content)->toContain('Description: A classic work on Stoic philosophy');

        return true;
    });
});
