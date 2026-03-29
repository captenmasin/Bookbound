<?php

use App\Models\Tag;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use App\Models\BookUser;
use App\Enums\UserBookStatus;
use Illuminate\Support\Collection;
use Laravel\Ai\Messages\UserMessage;
use App\Ai\Agents\BookRecommendationAgent;

uses(TestCase::class);

test('it recommends concrete books from the user library profile', function () {
    $user = User::factory()->make([
        'name' => 'Ada Reader',
    ]);

    $libraryBook = Book::factory()->make([
        'title' => 'Dune',
        'categories' => ['Science Fiction'],
        'language' => 'en',
    ]);
    $libraryBook->setRelation('pivot', new BookUser([
        'status' => UserBookStatus::Read->value,
        'created_at' => now()->subDays(5),
    ]));
    $libraryBook->setRelation('authors', new Collection([
        new Author(['id' => 10, 'name' => 'Frank Herbert']),
    ]));
    $libraryBook->setRelation('tags', new Collection([
        new Tag(['id' => 20, 'name' => 'Space Opera']),
    ]));

    BookRecommendationAgent::fake([
        ['recommendations' => [
            [
                'title' => 'Children of Time',
                'author' => 'Adrian Tchaikovsky',
                'published_year' => 2015,
                'reason' => 'A good fit if you want another science fiction novel with big-idea scope.',
            ],
        ]],
    ]);

    $recommendations = BookRecommendationAgent::make(
        user: $user,
        libraryBooks: new Collection([$libraryBook]),
    )->recommend();

    expect($recommendations)->toBe([
        [
            'title' => 'Children of Time',
            'author' => 'Adrian Tchaikovsky',
            'published_year' => 2015,
            'reason' => 'A good fit if you want another science fiction novel with big-idea scope.',
        ],
    ]);

    BookRecommendationAgent::assertPrompted(function ($prompt) {
        expect($prompt->prompt)->toBe('Recommend up to 5 books for this reader as concrete title and author suggestions.');

        $message = collect($prompt->agent->messages())->sole();

        expect($message)->toBeInstanceOf(UserMessage::class);
        expect($message->content)->toContain('Name: Ada Reader');
        expect($message->content)->toContain('Top authors: Frank Herbert (1)');
        expect($message->content)->toContain('Top tags: Space Opera (1)');
        expect($message->content)->toContain('Top categories: Science Fiction (1)');
        expect($message->content)->toContain('Already owned titles: Dune');

        return true;
    });
});
