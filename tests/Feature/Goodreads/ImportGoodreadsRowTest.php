<?php

use App\Models\Book;
use App\Models\Note;
use App\Models\User;
use App\Models\Author;
use App\Models\Rating;
use App\Models\Review;
use App\Models\GoodreadsImport;
use App\Jobs\ImportGoodreadsRow;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Config;
use App\Contracts\BookApiServiceInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function () {
    Queue::fake();
});

function runGoodreadsRowJob(ImportGoodreadsRow $job): void
{
    app()->call([$job, 'handle']);
}

test('it imports a new library entry from a matching identifier and upserts personal data', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create(['identifier' => '9780345538987']);
    $import = GoodreadsImport::factory()->for($user)->create();

    $job = new ImportGoodreadsRow($import->id, 2, [
        'Title' => 'Jurassic Park',
        'Author' => 'Michael Crichton',
        'Additional Authors' => '',
        'ISBN' => '="0345538986"',
        'ISBN13' => '="9780345538987"',
        'My Rating' => '5',
        'Date Read' => '2026/03/22',
        'Date Added' => '2025/12/29',
        'Bookshelves' => 'kindle',
        'Exclusive Shelf' => 'read',
        'My Review' => 'Still excellent.',
        'Private Notes' => 'Need the sequel next.',
    ]);

    runGoodreadsRowJob($job);

    $pivot = $user->books()->where('book_id', $book->id)->first()->pivot;

    expect($pivot->status)->toBe('Read')
        ->and($pivot->tags)->toBe(['Kindle'])
        ->and($pivot->created_at->toDateTimeString())->toBe('2025-12-29 00:00:00')
        ->and($pivot->read_at->toDateTimeString())->toBe('2026-03-22 00:00:00')
        ->and(Rating::where('book_id', $book->id)->where('user_id', $user->id)->value('value'))->toBe(5)
        ->and(Review::where('book_id', $book->id)->where('user_id', $user->id)->value('content'))->toBe('Still excellent.')
        ->and(Note::where('book_id', $book->id)->where('user_id', $user->id)->value('content'))->toBe('Need the sequel next.');

    $import->refresh();

    expect($import->imported_rows)->toBe(1)
        ->and($import->processed_rows)->toBe(1)
        ->and($import->failed_rows)->toBe(0);
});

test('it merges into an existing library entry and keeps the earliest added date', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create(['identifier' => '9780345404473']);
    $import = GoodreadsImport::factory()->for($user)->create();

    $user->books()->attach($book->id, [
        'status' => 'Plan to Read',
        'tags' => ['Owned'],
        'created_at' => '2024-01-01 00:00:00',
        'read_at' => null,
        'updated_at' => now(),
    ]);

    Note::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'book_status' => 'Plan to Read',
        'content' => 'Already noted.',
    ]);

    $job = new ImportGoodreadsRow($import->id, 2, [
        'Title' => 'It',
        'Author' => 'Stephen King',
        'Additional Authors' => '',
        'ISBN' => '="0345404475"',
        'ISBN13' => '="9780345404473"',
        'My Rating' => '4',
        'Date Read' => '2026/03/08',
        'Date Added' => '2025/12/29',
        'Bookshelves' => 'horror',
        'Exclusive Shelf' => 'read',
        'My Review' => 'A lot, but worth it.',
        'Private Notes' => 'Already noted.',
    ]);

    runGoodreadsRowJob($job);

    $pivot = $user->books()->where('book_id', $book->id)->first()->pivot;

    expect($pivot->status)->toBe('Read')
        ->and($pivot->tags)->toBe(['Owned', 'Horror'])
        ->and($pivot->created_at->toDateTimeString())->toBe('2024-01-01 00:00:00')
        ->and($pivot->read_at->toDateTimeString())->toBe('2026-03-08 00:00:00')
        ->and(Note::where('book_id', $book->id)->where('user_id', $user->id)->count())->toBe(1);

    $import->refresh();

    expect($import->merged_rows)->toBe(1)
        ->and($import->processed_rows)->toBe(1);
});

test('it blocks new books when the user has reached their plan limit', function () {
    Config::set('subscriptions.plans.free.limits.max_books', 0);

    $user = User::factory()->create();
    $book = Book::factory()->create(['identifier' => '9780345538987']);
    $import = GoodreadsImport::factory()->for($user)->create();

    $job = new ImportGoodreadsRow($import->id, 2, [
        'Title' => 'Jurassic Park',
        'Author' => 'Michael Crichton',
        'Additional Authors' => '',
        'ISBN' => '',
        'ISBN13' => '="9780345538987"',
        'My Rating' => '0',
        'Date Read' => '',
        'Date Added' => '2025/12/29',
        'Bookshelves' => '',
        'Exclusive Shelf' => 'to-read',
        'My Review' => '',
        'Private Notes' => '',
    ]);

    runGoodreadsRowJob($job);

    $import->refresh();

    expect($user->books()->count())->toBe(0)
        ->and($import->blocked_rows)->toBe(1)
        ->and($import->skipped_rows)->toBe(1)
        ->and($import->failures()->count())->toBe(1)
        ->and($import->failures()->first()->reason)->toContain('plan book limit');
});

test('it uses title and author fallback matching when no isbn is present', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create([
        'identifier' => '9780345391803',
        'title' => 'The Hitchhiker’s Guide to the Galaxy',
    ]);
    $author = Author::factory()->create(['name' => 'Douglas Adams']);
    $book->authors()->sync([$author->id]);

    $import = GoodreadsImport::factory()->for($user)->create();

    $mock = Mockery::mock(BookApiServiceInterface::class);
    $mock->shouldReceive('search')
        ->once()
        ->andReturn([
            'total' => 1,
            'items' => [[
                'isbn13' => '9780345391803',
                'title' => "The Hitchhiker's Guide to the Galaxy",
                'authors' => ['Douglas Adams'],
            ]],
        ]);

    $this->app->instance(BookApiServiceInterface::class, $mock);

    $job = new ImportGoodreadsRow($import->id, 2, [
        'Title' => "The Hitchhiker’s Guide to the Galaxy (Hitchhiker's Guide to the Galaxy, #1)",
        'Author' => 'Douglas Adams',
        'Additional Authors' => '',
        'ISBN' => '',
        'ISBN13' => '',
        'My Rating' => '0',
        'Date Read' => '',
        'Date Added' => '2025/12/29',
        'Bookshelves' => '',
        'Exclusive Shelf' => 'to-read',
        'My Review' => '',
        'Private Notes' => '',
    ]);

    runGoodreadsRowJob($job);

    expect($user->books()->where('book_id', $book->id)->exists())->toBeTrue();
});

test('it records a skipped row when no credible fallback match is found', function () {
    $user = User::factory()->create();
    $import = GoodreadsImport::factory()->for($user)->create();

    $mock = Mockery::mock(BookApiServiceInterface::class);
    $mock->shouldReceive('search')
        ->once()
        ->andReturn([
            'total' => 1,
            'items' => [[
                'isbn13' => '9781111111111',
                'title' => 'Completely Different Book',
                'authors' => ['Someone Else'],
            ]],
        ]);

    $this->app->instance(BookApiServiceInterface::class, $mock);

    $job = new ImportGoodreadsRow($import->id, 2, [
        'Title' => 'Red Dragon (Hannibal Lecter, #1)',
        'Author' => 'Thomas Harris',
        'Additional Authors' => '',
        'ISBN' => '',
        'ISBN13' => '',
        'My Rating' => '0',
        'Date Read' => '',
        'Date Added' => '2025/12/29',
        'Bookshelves' => '',
        'Exclusive Shelf' => 'to-read',
        'My Review' => '',
        'Private Notes' => '',
    ]);

    runGoodreadsRowJob($job);

    $import->refresh();

    expect($import->skipped_rows)->toBe(1)
        ->and($import->failures()->count())->toBe(1)
        ->and($import->failures()->first()->reason)->toContain('No credible title and author match');
});
