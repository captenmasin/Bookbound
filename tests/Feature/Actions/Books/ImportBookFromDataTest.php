<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use App\Actions\Books\ImportBookCover;
use App\Actions\Books\ImportBookFromData;
use App\Contracts\BookApiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ImportBookFromData', function () {
    test('throws when identifier is missing', function () {
        app(ImportBookFromData::class)->handle(['title' => 'No Identifier']);
    })->throws(InvalidArgumentException::class);

    test('returns existing book when not forced', function () {
        $book = Book::factory()->create(['identifier' => 'exists-123']);

        Queue::fake();

        $found = app(ImportBookFromData::class)->handle('exists-123');

        expect($found->id)->toBe($book->id);

        // No cover import should be queued since we returned early
        ImportBookCover::assertPushed(0);
    });

    test('imports book from data', function () {
        $identifier = 'identifier-12345';

        $payload = [
            'identifier' => $identifier,
            'codes' => [
                ['type' => 'ISBN_13', 'identifier' => '9780000000000'],
                ['type' => 'ISBN_10', 'identifier' => '0000000000'],
            ],
            'title' => 'The Cached Book',
            'pageCount' => 321,
            'edition' => 'Hardcover',
            'binding' => 'Print',
            'language' => 'eng',
            'published_date' => '2020-01-01',
            'description' => 'A fine description.',
            'cover' => 'https://example.test/cover.jpg',
            'authors' => [
                ['name' => 'Jane Doe'],
                ['name' => 'John Smith'],
            ],
            'tags' => [
                'Fiction/Adventure',
                'Young Adult',
            ],
            'publisher' => ['name' => 'Acme Publishing', 'uuid' => null],
            'service' => 'TestService',
        ];

        Queue::fake();

        $book = app(ImportBookFromData::class)->handle($payload);

        expect($book)
            ->not->toBeNull()
            ->and($book->identifier)->toBe($identifier)
            ->and($book->title)->toBe('The Cached Book')
            ->and($book->page_count)->toBe(321)
            ->and($book->edition)->toBe('Hardcover')
            ->and($book->binding)->toBe('Print')
            ->and($book->language)->toBe('eng')
            ->and($book->published_date)->toBe('2020-01-01')
            ->and($book->description)->toBe('A fine description.')
            ->and($book->service)->toBe('TestService');

        // Relations created
        $book->load(['authors', 'tags', 'publisher']);
        expect($book->authors->pluck('name')->sort()->values()->all())
            ->toBe(['Jane Doe', 'John Smith'])
            ->and($book->tags->pluck('name')->sort()->values()->all())
            ->toBe(['Adventure', 'Fiction', 'Young Adult'])
            ->and($book->publisher?->name)->toBe('Acme Publishing');

        // Cover import queued
        ImportBookCover::assertPushed(1);
    });

    test('imports book from cache when exists', function () {
        $identifier = 'cache-12345';

        // Prepare standardized payload (already transformed shape)
        $payload = [
            'identifier' => $identifier,
            'codes' => [
                ['type' => 'ISBN_13', 'identifier' => '9780000000000'],
                ['type' => 'ISBN_10', 'identifier' => '0000000000'],
            ],
            'title' => 'The Cached Book',
            'pageCount' => 321,
            'edition' => 'Hardcover',
            'binding' => 'Print',
            'language' => 'eng',
            'published_date' => '2020-01-01',
            'description' => 'A fine description.',
            'cover' => 'https://example.test/cover.jpg',
            'authors' => [
                ['name' => 'Jane Doe'],
                ['name' => 'John Smith'],
            ],
            'tags' => [
                'Fiction/Adventure',
                'Young Adult',
            ],
            'publisher' => ['name' => 'Acme Publishing', 'uuid' => null],
            'service' => 'TestService',
        ];

        Cache::put('book:'.$identifier, $payload);

        Queue::fake();

        $book = app(ImportBookFromData::class)->handle($identifier);

        expect($book)
            ->not->toBeNull()
            ->and($book->identifier)->toBe($identifier)
            ->and($book->title)->toBe('The Cached Book')
            ->and($book->page_count)->toBe(321)
            ->and($book->edition)->toBe('Hardcover')
            ->and($book->binding)->toBe('Print')
            ->and($book->language)->toBe('eng')
            ->and($book->published_date)->toBe('2020-01-01')
            ->and($book->description)->toBe('A fine description.')
            ->and($book->service)->toBe('TestService');

        // Relations created
        $book->load(['authors', 'tags', 'publisher']);
        expect($book->authors->pluck('name')->sort()->values()->all())
            ->toBe(['Jane Doe', 'John Smith'])
            ->and($book->tags->pluck('name')->sort()->values()->all())
            ->toBe(['Adventure', 'Fiction', 'Young Adult'])
            ->and($book->publisher?->name)->toBe('Acme Publishing');

        // Cover import queued
        ImportBookCover::assertPushed(1);
    });

    test('uses cache arrays to reuse existing authors/tags/publisher', function () {
        $identifier = 'cache-relations-1';

        // Pre-existing related models
        $existingAuthor = Author::factory()->create(['name' => 'Existing Author']);
        $existingTag = Tag::factory()->create(['name' => 'Existing Tag']);
        $existingPublisher = Publisher::factory()->create(['name' => 'Existing Pub']);

        // Build keyed collections for cache lookups
        $authorsCache = collect([$existingAuthor->name => $existingAuthor]);
        $tagsCache = collect([$existingTag->name => $existingTag]);
        $publishersCache = collect([$existingPublisher->name => $existingPublisher]);

        $payload = [
            'identifier' => $identifier,
            'codes' => [
                ['type' => 'ISBN_13', 'identifier' => '9781111111111'],
                ['type' => 'ISBN_10', 'identifier' => '1111111111'],
            ],
            'title' => 'Reused Relations Book',
            'published_date' => '2021-05-05',
            'description' => 'Testing cache reuse.',
            'authors' => [
                ['name' => 'Existing Author'],
            ],
            'tags' => [
                'Existing Tag',
            ],
            'publisher' => ['name' => 'Existing Pub', 'uuid' => null],
        ];

        Cache::put('book:'.$identifier, $payload);

        app(ImportBookFromData::class)->handle($identifier, false, [
            'authors' => $authorsCache,
            'tags' => $tagsCache,
            'publishers' => $publishersCache,
        ]);

        expect(Author::count())->toBe(1)
            ->and(Tag::count())->toBe(1)
            ->and(Publisher::count())->toBe(1);
    });

    test('calls API service if identifier is found but no data and no cache', function () {
        $identifier = 'import-12345';

        $mock = Mockery::mock(BookApiServiceInterface::class);
        $mock->shouldReceive('get')
            ->with($identifier)
            ->andReturn([
                'isbn' => $identifier,
                'title' => 'Harry Potter',
                'authors' => [
                    'J.K. Rowling',
                ],
                'published_date' => '1997-06-26',
                'description' => 'A young wizard embarks on an adventure.',
                'pageCount' => 223,
                'cover' => 'https://example.com/cover.jpg',
                'codes' => [
                    ['type' => 'ISBN_13', 'identifier' => '9780747532743'],
                    ['type' => 'ISBN_10', 'identifier' => '0747532745'],
                ],
            ]);

        $this->app->instance(BookApiServiceInterface::class, $mock);

        app(ImportBookFromData::class)->handle($identifier);

        $mock->shouldHaveReceived('get')
            ->with($identifier)
            ->once();
    });

    test('throws exception when cached data is empty after transformation', function () {
        $identifier = 'no-data-12345';

        // Mock the cache to simulate the scenario where data is empty after transformation
        Cache::shouldReceive('pull')
            ->with("book:$identifier")
            ->andReturn(null); // No cached book data

        Cache::shouldReceive('remember')
            ->with("books:id:$identifier", Mockery::any(), Mockery::any())
            ->andReturn(null); // Cache callback returns null/empty data

        expect(fn () => app(ImportBookFromData::class)->handle($identifier))
            ->toThrow(\Exception::class, "No data found for identifier: $identifier");
    });
});
