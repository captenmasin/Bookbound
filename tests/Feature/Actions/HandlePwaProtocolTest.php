<?php

use App\Models\Book;
use Illuminate\Http\Request;
use App\Actions\HandlePwaProtocol;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('HandlePwaProtocol', function () {
    test('handle() redirects to book page for book type with valid identifier', function () {
        $book = Book::factory()->create(['identifier' => 'test-book-123']);
        $request = Request::create('/');
        $action = app(HandlePwaProtocol::class);

        $response = $action->handle($request, 'book', '//test-book-123');

        expect($response->getStatusCode())->toBe(302)
            ->and($response->getTargetUrl())->toContain(route('books.show', $book));
    });

    test('handle() throws exception for book type with invalid identifier', function () {
        $request = Request::create('/');
        $action = app(HandlePwaProtocol::class);

        expect(fn () => $action->handle($request, 'book', '//nonexistent-book'))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    test('handle() redirects to search page for search type', function () {
        $request = Request::create('/');
        $action = app(HandlePwaProtocol::class);

        $response = $action->handle($request, 'search', '//science fiction');

        expect($response->getStatusCode())->toBe(302)
            ->and($response->getTargetUrl())->toContain('/books/search')
            ->and($response->getTargetUrl())->toContain('q=science%2Bfiction');
    });

    test('handle() processes data correctly by removing protocol prefix', function () {
        $book = Book::factory()->create(['identifier' => 'protocol-test']);
        $request = Request::create('/');
        $action = app(HandlePwaProtocol::class);

        $response = $action->handle($request, 'book', 'bookbound://protocol-test');

        expect($response->getStatusCode())->toBe(302)
            ->and($response->getTargetUrl())->toContain(route('books.show', $book));
    });

    test('handle() replaces spaces with plus signs in data', function () {
        $request = Request::create('/');
        $action = app(HandlePwaProtocol::class);

        $response = $action->handle($request, 'search', '//fantasy adventure');

        expect($response->getTargetUrl())->toContain('q=fantasy%2Badventure');
    });

    test('handle() works with complex search terms', function () {
        $request = Request::create('/');
        $action = app(HandlePwaProtocol::class);

        $response = $action->handle($request, 'search', '//the lord of the rings');

        expect($response->getStatusCode())->toBe(302)
            ->and($response->getTargetUrl())->toContain('/books/search')
            ->and($response->getTargetUrl())->toContain('q=the%2Blord%2Bof%2Bthe%2Brings');
    });

    test('handle() works with book identifiers containing special characters', function () {
        $book = Book::factory()->create(['identifier' => 'isbn-978-0-123456-78-9']);
        $request = Request::create('/');
        $action = app(HandlePwaProtocol::class);

        $response = $action->handle($request, 'book', '//isbn-978-0-123456-78-9');

        expect($response->getStatusCode())->toBe(302)
            ->and($response->getTargetUrl())->toContain(route('books.show', $book));
    });

    test('getBook() method finds book by identifier and redirects', function () {
        $book = Book::factory()->create(['identifier' => 'private-method-test']);
        $action = app(HandlePwaProtocol::class);
        $reflection = new \ReflectionClass($action);
        $method = $reflection->getMethod('getBook');
        $method->setAccessible(true);

        $response = $method->invoke($action, 'private-method-test');

        expect($response->getStatusCode())->toBe(302)
            ->and($response->getTargetUrl())->toContain(route('books.show', $book));
    });

    test('getSearch() method creates search redirect with query parameter', function () {
        $action = app(HandlePwaProtocol::class);
        $reflection = new \ReflectionClass($action);
        $method = $reflection->getMethod('getSearch');
        $method->setAccessible(true);

        $response = $method->invoke($action, 'mystery+novel');

        expect($response->getStatusCode())->toBe(302)
            ->and($response->getTargetUrl())->toContain('/books/search')
            ->and($response->getTargetUrl())->toContain('q=mystery%2Bnovel');
    });
});
