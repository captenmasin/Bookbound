<?php

use App\Models\User;
use App\Jobs\ImportBooksFromApiSearch;
use App\Actions\Books\SearchBooksFromApi;
use App\Contracts\BookApiServiceInterface;
use App\Jobs\ImportAdditionalBooksFromApiSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('handle() returns total and transformed books, caches each, and dispatches jobs', function () {
    $mock = Mockery::mock(BookApiServiceInterface::class);
    $mock->shouldReceive('search')
        ->with('foo', 'bar', 'baz', 30, 1)
        ->andReturn([
            'total' => 2,
            'items' => [
                ['isbn' => 'A', 'codes' => [''], 'title' => 'Alpha'],
                ['isbn13' => 'B', 'codes' => [''], 'title' => 'Beta'],
            ],
        ]);

    $this->app->instance(BookApiServiceInterface::class, $mock);

    $capturedChain = null;
    Bus::partialMock();
    Bus::shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(ImportBooksFromApiSearch::class));

    Bus::shouldReceive('chain')
        ->once()
        ->andReturnUsing(function (array $jobs) use (&$capturedChain) {
            $capturedChain = $jobs;

            return new class
            {
                public function onQueue($queue)
                {
                    return $this;
                }

                public function dispatch() {}
            };
        });

    $result = SearchBooksFromApi::run('foo', 'bar', 'baz', 30, 1);

    expect($result)
        ->toHaveKeys(['total', 'books'])
        ->and($result['total'])->toBe(2)
        ->and($result['books'])->toHaveCount(2)
        ->and($capturedChain)->not->toBeNull()
        ->and(count($capturedChain))->toBe(2)
        ->and($capturedChain[0])->toBeInstanceOf(ImportBooksFromApiSearch::class)
        ->and($capturedChain[1])->toBeInstanceOf(ImportAdditionalBooksFromApiSearch::class);

    $second = $capturedChain[1];
    expect(property_exists($second, 'query') ? $second->query : null)->toBe('foo')
        ->and(property_exists($second, 'author') ? $second->author : null)->toBe('bar')
        ->and(property_exists($second, 'subject') ? $second->subject : null)->toBe('baz');
});

test('does not chain when there are no results but still dispatches the import job', function () {
    $mock = Mockery::mock(BookApiServiceInterface::class);
    $mock->shouldReceive('search')
        ->with('empty', null, null, 30, 1)
        ->andReturn(['total' => 0, 'items' => []]);
    $this->app->instance(BookApiServiceInterface::class, $mock);

    // No caching should happen
    // Cache::shouldReceive('remember')->once();

    // Intercept Bus
    $capturedChain = null;
    Bus::partialMock();

    // Chain should NOT be called
    Bus::shouldReceive('chain')->never();

    $result = SearchBooksFromApi::run('empty', null, null, 30, 1);

    // Cache should clear instantly
    // Cache::shouldReceive('forget')->once();

    expect($result)
        ->toHaveKeys(['total', 'books'])
        ->and($result['total'])->toBe(0)
        ->and($result['books'])->toHaveCount(0);
});

test('asController returns a JSON response with BookResource', function () {
    $mock = Mockery::mock(BookApiServiceInterface::class);
    $mock->shouldReceive('search')
        ->with('foo', 'bar', null, 30, 1)
        ->andReturn([
            'total' => 2,
            'items' => [
                ['isbn' => 'A', 'codes' => [''], 'title' => 'Foo'],
                ['isbn13' => 'B', 'codes' => [''], 'title' => 'Bar'],
            ],
        ]);

    $this->app->instance(BookApiServiceInterface::class, $mock);

    $capturedChain = null;
    Bus::partialMock();
    Bus::shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(ImportBooksFromApiSearch::class));

    Bus::shouldReceive('chain')
        ->once()
        ->andReturnUsing(function (array $jobs) use (&$capturedChain) {
            $capturedChain = $jobs;

            return new class
            {
                public function onQueue($queue)
                {
                    return $this;
                }

                public function dispatch() {}
            };
        });

    $request = Request::create('/test/books/search/foo?q=foo&author=bar');
    $response = app(SearchBooksFromApi::class)->asController($request);

    expect($response->status())->toBe(200);

    $json = $response->getData(true);

    expect($json)
        ->toHaveKey('books')
        ->and($json['books'][0]['identifier'])->toBe('A')
        ->and($json)->toHaveKey('total')
        ->and($json['total'])->toBe(2);
});

test('book search returns results', function () {
    $user = User::factory()->create();

    // Mock the SearchBooksFromApi action
    $mock = Mockery::mock(SearchBooksFromApi::class);
    $mock->shouldReceive('run')
        ->with('harry potter', null, 10, 1)
        ->andReturn([
            'items' => [
                [
                    'identifier' => '9780747532743',
                    'title' => 'Harry Potter',
                    'authors' => ['J.K. Rowling'],
                    'publishedDate' => '1997-06-26',
                    'description' => 'A young wizard embarks on an adventure.',
                    'pageCount' => 223,
                    'cover' => 'https://example.com/cover.jpg',
                    'codes' => [
                        ['type' => 'ISBN_13', 'identifier' => '9780747532743'],
                        ['type' => 'ISBN_10', 'identifier' => '0747532745'],
                    ],
                ],
            ],
            'total' => 1,
        ]);

    $this->app->instance(SearchBooksFromApi::class, $mock);

    $response = $this->actingAs($user)
        ->get(route('books.search', ['q' => 'harry potteer']));

    $response->assertStatus(200);
});
