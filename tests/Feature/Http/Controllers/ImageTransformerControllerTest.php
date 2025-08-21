<?php

use function Pest\Laravel\get;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\ImageTransformerController;

describe('ImageTransformerController', function () {
    beforeEach(function () {
        // Set up configuration for testing
        Config::set('image-transform.public_path', 'storage');
        Config::set('image-transform.cache.enabled', false);
        Config::set('image-transform.rate_limit.enabled', false);
        Config::set('image-transform.enabled_options', [
            'width', 'height', 'format', 'quality', 'blur', 'contrast',
            'flip', 'pixelate', 'rotate', 'background', 'scale', 'crop',
        ]);
    });

    it('returns 404 for non-existent image', function () {
        $response = get('/image-transform/width=100/non-existent-image.jpg');

        $response->assertNotFound();
    });

    it('returns 404 for path outside public directory', function () {
        $response = get('/image-transform/width=100/../../../etc/passwd');

        $response->assertNotFound();
    });

    it('parses options correctly', function () {
        // Use reflection to test the private method
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('parseOptions');
        $method->setAccessible(true);

        $options = $method->invoke($controller, 'width=100,height=200,quality=80');

        expect($options)->toBe([
            'width' => 100,
            'height' => 200,
            'quality' => 80,
        ]);
    });

    it('parses complex options correctly', function () {
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('parseOptions');
        $method->setAccessible(true);

        $options = $method->invoke($controller, 'width=300,height=200,format=webp,quality=85,blur=2');

        expect($options)->toBe([
            'width' => 300,
            'height' => 200,
            'format' => 'webp',
            'quality' => 85,
            'blur' => 2,
        ]);
    });

    it('filters invalid options', function () {
        // Temporarily disable an option
        Config::set('image-transform.enabled_options', ['width', 'height']);

        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('parseOptions');
        $method->setAccessible(true);

        $options = $method->invoke($controller, 'width=100,height=200,blur=5');

        expect($options)->toBe([
            'width' => 100,
            'height' => 200,
        ]);
    });

    it('ignores options with wrong data types', function () {
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('parseOptions');
        $method->setAccessible(true);

        // width should be int, format should be string
        $options = $method->invoke($controller, 'width=abc,format=100');

        expect($options)->toBe([]);
    });

    it('respects maximum dimension limits', function () {
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getPositiveIntOptionValue');
        $method->setAccessible(true);

        $result = $method->invoke($controller, ['width' => 5000], 'width', 2000);

        expect($result)->toBe(2000);
    });

    it('handles positive integer values correctly', function () {
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getPositiveIntOptionValue');
        $method->setAccessible(true);

        // Test positive value within limit
        $result = $method->invoke($controller, ['width' => 100], 'width', 200);
        expect($result)->toBe(100);

        // Test zero value (should return null)
        $result = $method->invoke($controller, ['width' => 0], 'width', 200);
        expect($result)->toBeNull();

        // Test negative value (should return null)
        $result = $method->invoke($controller, ['width' => -10], 'width', 200);
        expect($result)->toBeNull();
    });

    it('clamps contrast values within bounds', function () {
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getUnsignedIntOptionValue');
        $method->setAccessible(true);

        // Test value above max
        $result = $method->invoke($controller, ['contrast' => 150], 'contrast', 0, -100, 100);
        expect($result)->toBe(100);

        // Test value below min
        $result = $method->invoke($controller, ['contrast' => -150], 'contrast', 0, -100, 100);
        expect($result)->toBe(-100);

        // Test value within range
        $result = $method->invoke($controller, ['contrast' => 50], 'contrast', 0, -100, 100);
        expect($result)->toBe(50);
    });

    it('handles integer option values with max limit', function () {
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getIntOptionValue');
        $method->setAccessible(true);

        // Test value within limit (rotation uses 359 as max)
        $result = $method->invoke($controller, ['rotate' => 180], 'rotate', 0);
        expect($result)->toBe(180);

        // Test value above limit
        $result = $method->invoke($controller, ['rotate' => 400], 'rotate', 0);
        expect($result)->toBe(359);

        // Test fallback value
        $result = $method->invoke($controller, [], 'rotate', 90);
        expect($result)->toBe(90);
    });

    it('handles string option values', function () {
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getStringOptionValue');
        $method->setAccessible(true);

        // Test existing value
        $result = $method->invoke($controller, ['format' => 'webp'], 'format', 'jpeg');
        expect($result)->toBe('webp');

        // Test fallback value
        $result = $method->invoke($controller, [], 'format', 'jpeg');
        expect($result)->toBe('jpeg');

        // Test null default
        $result = $method->invoke($controller, [], 'format', null);
        expect($result)->toBeNull();
    });

    it('handles select option values with allowed values', function () {
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getSelectOptionValue');
        $method->setAccessible(true);

        // Test valid value
        $result = $method->invoke($controller, ['flip' => 'h'], 'flip', ['h', 'v', 'hv'], null);
        expect($result)->toBe('h');

        // Test invalid value (should return null)
        $result = $method->invoke($controller, ['flip' => 'invalid'], 'flip', ['h', 'v', 'hv'], null);
        expect($result)->toBeNull();

        // Test default value
        $result = $method->invoke($controller, [], 'flip', ['h', 'v', 'hv'], 'h');
        expect($result)->toBe('h');
    });

    it('generates correct cache path', function () {
        Config::set('image-transform.cache.disk', 'local');

        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getCachePath');
        $method->setAccessible(true);

        $options = ['width' => 100, 'height' => 200];
        $result = $method->invoke($controller, 'test-image.jpg', $options);

        // Should contain the hashed options and original path
        expect($result)->toContain('_cache/image-transform');
        expect($result)->toContain('test-image.jpg');
    });

    it('creates image response with correct headers', function () {
        Config::set('image-transform.cache.enabled', true);
        Config::set('image-transform.headers', [
            'Cache-Control' => 'public, max-age=31536000',
        ]);

        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('imageResponse');
        $method->setAccessible(true);

        $response = $method->invoke($controller, 'fake-image-data', 'image/jpeg', true);

        expect($response->getContent())->toBe('fake-image-data');
        expect($response->headers->get('Content-Type'))->toBe('image/jpeg');
        expect($response->headers->get('X-Cache'))->toBe('HIT');
        expect($response->headers->get('Cache-Control'))->toContain('max-age=31536000');
    });

    it('creates image response without cache headers when caching disabled', function () {
        Config::set('image-transform.cache.enabled', false);

        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('imageResponse');
        $method->setAccessible(true);

        $response = $method->invoke($controller, 'fake-image-data', 'image/png', false);

        expect($response->getContent())->toBe('fake-image-data');
        expect($response->headers->get('Content-Type'))->toBe('image/png');
        expect($response->headers->has('X-Cache'))->toBeFalse();
    });

    it('validates allowed options enum', function () {
        $allowedOptions = \App\Enums\AllowedOptions::all();

        expect($allowedOptions)->toContain('width');
        expect($allowedOptions)->toContain('height');
        expect($allowedOptions)->toContain('format');
        expect($allowedOptions)->toContain('quality');
        expect($allowedOptions)->toContain('blur');
        expect($allowedOptions)->toContain('contrast');
        expect($allowedOptions)->toContain('flip');
        expect($allowedOptions)->toContain('pixelate');
        expect($allowedOptions)->toContain('rotate');
        expect($allowedOptions)->toContain('background');
        expect($allowedOptions)->toContain('scale');
        expect($allowedOptions)->toContain('crop');
    });

    it('validates allowed mime types enum', function () {
        $allowedMimeTypes = \App\Enums\AllowedMimeTypes::all();

        expect($allowedMimeTypes)->toContain('image/jpeg');
        expect($allowedMimeTypes)->toContain('image/png');
        expect($allowedMimeTypes)->toContain('image/webp');
        expect($allowedMimeTypes)->toContain('image/gif');
    });

    it('handles empty options string', function () {
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('parseOptions');
        $method->setAccessible(true);

        $options = $method->invoke($controller, '');

        expect($options)->toBe([]);
    });

    it('handles options without values', function () {
        $controller = new ImageTransformerController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('parseOptions');
        $method->setAccessible(true);

        // Options without = should be ignored
        $options = $method->invoke($controller, 'width,height=200');

        expect($options)->toBe([
            'height' => 200,
        ]);
    });
});
