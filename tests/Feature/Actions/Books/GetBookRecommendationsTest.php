<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use App\Enums\UserBookStatus;
use App\Actions\Books\ImportBookFromData;
use App\Actions\Books\SearchBooksFromApi;
use App\Ai\Agents\BookRecommendationAgent;
use App\Actions\Books\GetBookRecommendations;

it('returns a locally matched recommendation for a user library', function () {
    $user = User::factory()->create();
    $sharedAuthor = Author::factory()->create([
        'name' => 'Frank Herbert',
    ]);
    $sharedTag = Tag::factory()->create([
        'name' => 'Space Opera',
    ]);

    $readBook = Book::factory()->create([
        'title' => 'Dune',
        'categories' => ['Science Fiction'],
        'language' => 'en',
    ]);
    $readingBook = Book::factory()->create([
        'title' => 'Hyperion',
        'categories' => ['Science Fiction'],
        'language' => 'en',
    ]);
    $candidateBook = Book::factory()->create([
        'title' => 'Children of Time',
        'categories' => ['Science Fiction'],
        'language' => 'en',
    ]);
    $candidateAuthor = Author::factory()->create([
        'name' => 'Adrian Tchaikovsky',
    ]);

    $readBook->authors()->attach($sharedAuthor);
    $readBook->tags()->attach($sharedTag);
    $readingBook->authors()->attach($sharedAuthor);
    $readingBook->tags()->attach($sharedTag);
    $candidateBook->authors()->attach($candidateAuthor);
    $candidateBook->tags()->attach($sharedTag);

    $user->books()->attach($readBook, ['status' => UserBookStatus::Read->value]);
    $user->books()->attach($readingBook, ['status' => UserBookStatus::Reading->value]);

    BookRecommendationAgent::fake([
        ['recommendations' => [
            [
                'title' => 'Children of Time',
                'author' => 'Adrian Tchaikovsky',
                'published_year' => 2015,
                'reason' => 'Fits your recent run of big-idea science fiction.',
            ],
        ]],
    ]);

    $recommendations = app(GetBookRecommendations::class)->handle($user);

    expect($recommendations)->toHaveCount(1)
        ->and($recommendations[0]['book']->is($candidateBook))->toBeTrue()
        ->and($recommendations[0]['reason'])->toBe('Fits your recent run of big-idea science fiction.');
});

it('imports a missing recommendation from the api and does not add it to the user library', function () {
    $user = User::factory()->create();
    $sharedAuthor = Author::factory()->create([
        'name' => 'Octavia E. Butler',
    ]);
    $sharedTag = Tag::factory()->create([
        'name' => 'Speculative Fiction',
    ]);

    $readBook = Book::factory()->create([
        'title' => 'Kindred',
        'categories' => ['Science Fiction'],
        'language' => 'en',
    ]);
    $readingBook = Book::factory()->create([
        'title' => 'Parable of the Sower',
        'categories' => ['Science Fiction'],
        'language' => 'en',
    ]);
    $importedBook = Book::factory()->create([
        'identifier' => 'imported-dawn',
        'title' => 'Dawn',
        'categories' => ['Science Fiction'],
        'language' => 'en',
    ]);

    $readBook->authors()->attach($sharedAuthor);
    $readBook->tags()->attach($sharedTag);
    $readingBook->authors()->attach($sharedAuthor);
    $readingBook->tags()->attach($sharedTag);

    $user->books()->attach($readBook, ['status' => UserBookStatus::Read->value]);
    $user->books()->attach($readingBook, ['status' => UserBookStatus::Reading->value]);

    BookRecommendationAgent::fake([
        ['recommendations' => [
            [
                'title' => 'Dawn',
                'author' => 'Octavia E. Butler',
                'published_year' => 1987,
                'reason' => 'Fits your taste for speculative fiction with philosophical weight.',
            ],
        ]],
    ]);

    $searchMock = Mockery::mock(SearchBooksFromApi::class);
    $searchMock->shouldReceive('handle')
        ->once()
        ->with('Dawn', 'Octavia E. Butler', null, 10, 1)
        ->andReturn([
            'total' => 1,
            'books' => [[
                'identifier' => 'imported-dawn',
                'codes' => [
                    ['type' => 'ISBN_13', 'identifier' => '9780446676106'],
                ],
                'title' => 'Dawn',
                'authors' => [
                    ['name' => 'Octavia E. Butler'],
                ],
                'tags' => ['Science Fiction'],
                'published_date' => '1987-01-01',
                'description' => 'A science fiction classic.',
                'language' => 'en',
                'service' => 'GoogleBooks',
            ]],
        ]);
    app()->instance(SearchBooksFromApi::class, $searchMock);

    ImportBookFromData::mock()
        ->shouldReceive('handle')
        ->once()
        ->andReturn($importedBook);

    $recommendations = app(GetBookRecommendations::class)->handle($user);

    expect($recommendations)->toHaveCount(1)
        ->and($recommendations[0]['book']->is($importedBook))->toBeTrue()
        ->and($recommendations[0]['reason'])->toBe('Fits your taste for speculative fiction with philosophical weight.');

    expect($user->fresh()->books()->whereKey($importedBook->id)->exists())->toBeFalse();
});
