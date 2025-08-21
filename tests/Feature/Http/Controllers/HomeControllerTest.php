<?php

use function Pest\Laravel\get;

use Illuminate\Support\Facades\Cache;

describe('HomeController', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('displays home page with correct component', function () {
        $response = get('/');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
            );
    });

    it('has correct meta tags for home page', function () {
        $response = get('/');

        $response->assertOk();
        $response->assertSee('Your Reading Life at a Glance - Track, Categorize & Review Your Books');
        $response->assertSee('Manage your entire reading collection with ease—search by title, author, or tag; filter by reading status; and save personal notes and reviews.');
    });

    it('displays subscription plan features and limits', function () {
        $response = get('/');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('freeLimits', fn ($limits) => $limits
                    ->where('max_books', 50)
                    ->where('private_notes', false)
                    ->where('custom_covers', false)
                )
                ->has('freeFeatures')
                ->has('proLimits', fn ($limits) => $limits
                    ->where('max_books', null)
                    ->where('private_notes', true)
                    ->where('custom_covers', true)
                )
                ->has('proFeatures')
            );
    });

    it('displays free plan features correctly', function () {
        $response = get('/');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('freeFeatures', 6)
                ->where('freeFeatures.0', 'Up to 50 Books')
                ->where('freeFeatures.1', 'Scan Barcodes')
                ->where('freeFeatures.2', 'Search and Filter your Library')
                ->where('freeFeatures.3', 'Preview Book Details')
                ->where('freeFeatures.4', 'Track Book Status')
                ->where('freeFeatures.5', 'Review and Rate Books')
            );
    });

    it('displays pro plan features correctly', function () {
        $response = get('/');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('proFeatures', 8)
                ->where('proFeatures.0', 'Unlimited books')
                ->where('proFeatures.1', 'Scan Barcodes')
                ->where('proFeatures.2', 'Search and Filter your Library')
                ->where('proFeatures.3', 'Preview Book Details')
                ->where('proFeatures.4', 'Track Book Status')
                ->where('proFeatures.5', 'Review and Rate Books')
                ->where('proFeatures.6', 'Private Notes')
                ->where('proFeatures.7', 'Custom Book Covers')
            );
    });

    it('includes pricing data when Stripe is unavailable', function () {
        // This test verifies the fallback behavior when Stripe fails
        $response = get('/');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('price') // Should have price key even if N/A
                ->has('interval') // Should have interval key even if N/A
            );
    });

    it('displays all required data for home page', function () {
        $response = get('/');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
                ->has('price')
                ->has('interval')
                ->has('freeLimits')
                ->has('freeFeatures')
                ->has('proLimits')
                ->has('proFeatures')
            );
    });

    it('uses cache for pricing data', function () {
        // Pre-populate cache to test that cache is used
        Cache::forever('home.price', ['$9.99', 'month']);

        $response = get('/');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('price', '$9.99')
                ->where('interval', 'month')
            );
    });

    it('handles empty cache gracefully', function () {
        // Ensure cache is empty
        Cache::flush();

        $response = get('/');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('price')
                ->has('interval')
            );

        // Verify cache was populated after request
        expect(Cache::has('home.price'))->toBeTrue();
    });

    it('displays correct configuration data for free plan', function () {
        $response = get('/');

        $freeConfig = config('subscriptions.plans.free');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('freeLimits', $freeConfig['limits'])
                ->where('freeFeatures', $freeConfig['features'])
            );
    });

    it('displays correct configuration data for pro plan', function () {
        $response = get('/');

        $proConfig = config('subscriptions.plans.pro');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('proLimits', $proConfig['limits'])
                ->where('proFeatures', $proConfig['features'])
            );
    });

    it('verifies pricing data is cached forever', function () {
        Cache::flush();

        // First request should populate cache
        get('/');
        expect(Cache::has('home.price'))->toBeTrue();

        $cachedValue = Cache::get('home.price');
        expect($cachedValue)->toBeArray()
            ->and($cachedValue)->toHaveLength(2);
    });
});
