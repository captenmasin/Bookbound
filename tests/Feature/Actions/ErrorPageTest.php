<?php

use App\Actions\ErrorPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ErrorPage', function () {
    test('handle() returns 404 response by default', function () {
        $action = app(ErrorPage::class);

        $response = $action->handle();

        expect($response->getStatusCode())->toBe(404);
    });

    test('handle() returns 404 when given a string parameter', function () {
        $action = app(ErrorPage::class);

        $response = $action->handle('some-string');

        expect($response->getStatusCode())->toBe(404);
    });

    test('handle() returns custom status code when provided', function () {
        $action = app(ErrorPage::class);

        $response = $action->handle(500);

        expect($response->getStatusCode())->toBe(500);
    });

    test('handle() renders Error page with correct props', function () {
        $action = app(ErrorPage::class);

        $response = $action->handle();

        expect($response->getContent())->toContain('Error');
    });

    test('handle() includes correct breadcrumbs in response', function () {
        $action = app(ErrorPage::class);

        $response = $action->handle();
        $content = $response->getContent();

        expect($content)->toContain('Dashboard')
            ->and($content)->toContain('Page not found');
    });

    test('handle() sets correct page title meta', function () {
        $action = app(ErrorPage::class);

        $response = $action->handle();
        $content = $response->getContent();

        expect($content)->toContain('Not found');
    });

    test('handle() works with different HTTP status codes', function ($statusCode) {
        $action = app(ErrorPage::class);

        $response = $action->handle($statusCode);

        expect($response->getStatusCode())->toBe($statusCode);
    })->with([
        400,
        401,
        403,
        404,
        500,
        503,
    ]);

    test('handle() includes shared inertia data', function () {
        $action = app(ErrorPage::class);

        $response = $action->handle();
        $content = $response->getContent();

        // Should include app data from HandleInertiaRequests middleware
        expect($content)->toContain(config('app.name'));
    });
});
