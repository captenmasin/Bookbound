<?php

use function Pest\Laravel\get;

describe('Native runtime shared props', function () {
    it('marks normal web requests as non-native', function () {
        config()->set('nativephp-internal.running', false);
        config()->set('nativephp-internal.platform', null);

        $response = get(route('login'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('app.is_native', false)
                ->where('app.native_platform', null)
                ->where('app.is_mobile_shell', false)
                ->where('app.native_capabilities.scanner', false)
                ->where('app.native_capabilities.share', false)
            );
    });

    it('shares native runtime props for android shell requests', function () {
        config()->set('nativephp-internal.running', true);
        config()->set('nativephp-internal.platform', 'android');

        $response = get(route('login'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('app.is_native', true)
                ->where('app.native_platform', 'android')
                ->where('app.is_mobile_shell', true)
                ->where('app.native_capabilities.scanner', true)
                ->where('app.native_capabilities.share', true)
                ->where('app.native_capabilities.device', true)
                ->where('app.native_capabilities.system', true)
                ->where('app.native_capabilities.secure_storage', true)
            );
    });

    it('resolves pwa state and device separately from native runtime', function () {
        config()->set('nativephp-internal.running', false);
        config()->set('nativephp-internal.platform', null);

        $response = get(route('login'), [
            'X-PWA-Mode' => 'true',
            'X-PWA-Device' => 'ios',
        ]);

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('app.is_pwa', true)
                ->where('app.pwa_platform', 'ios')
                ->where('app.is_native', false)
                ->where('app.native_platform', null)
                ->where('app.is_mobile_shell', true)
            );
    });

    it('uses the expected Bookbound deep link configuration', function () {
        expect(config('nativephp.app_id'))->toBe('com.bookbound.mobile')
            ->and(config('nativephp.deeplink_scheme'))->toBe('bookbound')
            ->and(config('nativephp.deeplink_host'))->toBe(parse_url(config('app.url'), PHP_URL_HOST))
            ->and(config('nativephp.start_url'))->toBe('/login');
    });

    it('excludes local development artifacts from the native bundle', function () {
        expect(config('nativephp.cleanup_exclude_files'))->toContain(
            'database/database.sqlite',
            'frankenphp',
            '.DS_Store',
            'public/.DS_Store',
            'public/images/pwa/.DS_Store',
        );
    });
});
