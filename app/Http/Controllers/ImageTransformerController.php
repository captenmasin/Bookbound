<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Enums\AllowedOptions;
use Illuminate\Http\Response;
use App\Enums\AllowedMimeTypes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\RateLimiter;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;

class ImageTransformerController extends \Illuminate\Routing\Controller
{
    public function __invoke(Request $request, string $options, string $path)
    {
        $pathPrefix = config()->string('image-transform.public_path');

        $publicPath = public_path($pathPrefix.'/'.$path);

        abort_unless($publicPath, 404);

        // Check if the file exists
        abort_unless(File::exists($publicPath), 404);

        abort_unless(Str::startsWith($publicPath, public_path($pathPrefix)), 404);

        abort_unless(in_array(File::mimeType($publicPath), AllowedMimeTypes::all(), true), 404);

        $options = $this->parseOptions($options);

        // Check cache
        if (config()->boolean('image-transform.cache.enabled')) {
            $cachePath = $this->getCachePath($path, $options);

            if (File::exists($cachePath)) {
                if (Cache::has('image-transform:'.$cachePath)) {
                    // serve file from storage
                    return $this->imageResponse(
                        imageContent: File::get($cachePath),
                        mimeType: File::mimeType($cachePath),
                        cacheHit: true
                    );
                } else {
                    // Cache expired, delete the cache file and continue
                    File::delete($cachePath);
                }
            }
        }

        if (
            config()->boolean('image-transform.rate_limit.enabled') &&
            ! in_array(App::environment(), config()->array('image-transform.rate_limit.disabled_for_environments'))) {
            $this->rateLimit($request, $path);
        }

        $image = Image::read($publicPath);

        if (Arr::hasAny($options, ['width', 'height'])) {
            $scale = $this->getSelectOptionValue($options, 'scale', ['true', 'false'], 'true');
            $crop = $this->getSelectOptionValue($options, 'crop', ['top', 'bottom', 'left', 'right', 'center'], null);

            $width = $this->getPositiveIntOptionValue($options, 'width', $image->width() * 2);
            $height = $this->getPositiveIntOptionValue($options, 'height', $image->height() * 2);

            if ($scale === 'false' && $width && $height) {
                if ($crop) {
                    $image->cover($width, $height, $crop);
                } else {
                    $image->resize($width, $height);
                }
                //                $image->resize($width, $height);
                //                $image->cover($width, $height, 'le');
            } else {
                $image->scale($width, $height);
            }
        }

        if (Arr::has($options, 'blur')) {
            $image->blur($this->getPositiveIntOptionValue($options, 'blur', 100));
        }

        if (Arr::has($options, 'contrast')) {
            $image->contrast($this->getUnsignedIntOptionValue($options, 'contrast', 0, -100, 100));
        }

        if (Arr::has($options, 'flip')) {
            $flip = $this->getSelectOptionValue($options, 'flip', ['h', 'v', 'hv'], 'h');

            match ($flip) {
                'h' => $image->flip(),
                'v' => $image->flop(),
                'hv' => $image->flip()->flop(),
                default => null,
            };
        }

        if (Arr::has($options, 'pixelate')) {
            $image->pixelate($this->getPositiveIntOptionValue($options, 'pixelate', 100));
        }

        if (Arr::has($options, 'rotate')) {
            $image->rotate($this->getIntOptionValue($options, 'rotate', 0));
        }

        if (Arr::has($options, 'background')) {
            $backgroundColor = $this->getStringOptionValue($options, 'background', 'ffffff');

            if (! preg_match('/^([a-f0-9]{6}|[a-f0-9]{3})$/', $backgroundColor)) {
                $backgroundColor = null;
            }

            if ($backgroundColor) {
                $image->blendTransparency($backgroundColor);
            }

        }

        // We use the mime type instead of the extension to determine the format, because this is more reliable.
        $originalMimetype = File::mimeType($publicPath);

        $format = $this->getStringOptionValue($options, 'format', $originalMimetype);
        $quality = $this->getPositiveIntOptionValue($options, 'quality', 100, 100);

        $encoder = match ($format) {
            'png', 'image/png' => new PngEncoder,
            'webp', 'image/webp' => new WebpEncoder($quality),
            'jpeg', 'jpg', 'image/jpeg' => new JpegEncoder($quality),
            'gif', 'image/gif' => new GifEncoder,
            default => new AutoEncoder(quality: $quality),
        };

        $encoded = $image->encode($encoder);

        if (config()->boolean('image-transform.cache.enabled')) {
            defer(function () use ($path, $options, $encoded) {

                $cachePath = $this->getCachePath($path, $options);

                $cacheDir = dirname($cachePath);

                File::ensureDirectoryExists($cacheDir);
                File::put($cachePath, $encoded->toString());

                Cache::put(
                    key: 'image-transform:'.$cachePath,
                    value: true,
                    ttl: config()->integer('image-transform.cache.lifetime'),
                );
            });
        }

        return $this->imageResponse(
            imageContent: $encoded->toString(),
            mimeType: $encoded->mimetype(),
            cacheHit: false
        );

    }

    /**
     * Rate limit the request.
     */
    protected function rateLimit(Request $request, string $path): void
    {
        $key = 'image-transform:'.$request->ip().':'.$path;

        $passed = RateLimiter::attempt(
            key: $key,
            maxAttempts: config()->integer('image-transform.rate_limit.max_attempts'),
            callback: fn () => true,
            decaySeconds: config()->integer('image-transform.rate_limit.decay_seconds'),
        );

        abort_unless($passed, 429, 'Too many requests. Please try again later.');
    }

    /**
     * Parse the given options.
     *
     * @return array<string, int>
     */
    protected static function parseOptions(string $options): array
    {
        /**
         *  The allowed options and their PHP types.
         *
         * @var array<string, string>
         */
        $allowedOptions = AllowedOptions::withTypes();

        $options = explode(',', $options);

        return collect($options)
            ->mapWithKeys(function ($option) {
                [$key] = explode('=', $option);

                $value = explode('=', $option)[1] ?? null;

                $value = is_numeric($value) ? (int) $value : $value;

                return [$key => $value];
            })
            ->filter(function ($value, $key) use ($allowedOptions) {
                return array_key_exists($key, $allowedOptions) && gettype($value) === $allowedOptions[$key];
            })->filter(function ($value, $key) {
                return in_array($key, config()->array('image-transform.enabled_options'), true);
            })->toArray();
    }

    /**
     * Get the cache path for the given path and options.
     */
    protected static function getCachePath(string $path, array $options): string
    {
        $pathPrefix = config()->string('image-transform.public_path');

        $optionsHash = md5(json_encode($options));

        return Storage::disk(config()->string('image-transform.cache.disk'))->path('_cache/image-transform/'.$pathPrefix.'/'.$optionsHash.'_'.$path);
    }

    /**
     * Respond with the image content.
     */
    protected static function imageResponse(string $imageContent, string $mimeType, bool $cacheHit = false): Response
    {
        return response($imageContent, 200, [
            'Content-Type' => $mimeType,
            ...(config()->boolean('image-transform.cache.enabled') ? [
                'X-Cache' => $cacheHit ? 'HIT' : 'MISS',
            ] : []),
            ...(config()->array('image-transform.headers')),
        ]);
    }

    protected static function getPositiveIntOptionValue(array $options, string $option, ?int $max = null, ?int $fallback = null): ?int
    {
        $value = min(
            Arr::get($options, $option, $fallback),
            $max ?? PHP_INT_MAX,
        );

        return $value > 0 ? $value : null;
    }

    protected static function getIntOptionValue(array $options, string $option, ?int $fallback = null): ?int
    {
        $max = 359;
        $value = min(
            Arr::get($options, $option, $fallback),
            $max ?? PHP_INT_MAX,
        );

        return $value;
    }

    /**
     * Get the unsigned int value of the given option.
     */
    protected static function getUnsignedIntOptionValue(array $options, string $option, ?int $fallback = null, ?int $min = null, ?int $max = null): ?int
    {
        return min(
            max(
                Arr::get($options, $option, $fallback),
                $min ?? PHP_INT_MIN,
            ),
            $max ?? PHP_INT_MAX,
        );
    }

    /**
     * Get the string value of the given option.
     */
    protected static function getStringOptionValue(array $options, string $option, ?string $default = null): ?string
    {
        return Arr::get($options, $option, $default);

    }

    /**
     * Get the select option value of the given option.
     *
     * @param  array<string>  $allowedValues
     */
    protected static function getSelectOptionValue(array $options, string $option, array $allowedValues, ?string $default = null): ?string
    {
        $value = Arr::get($options, $option, $default);

        return in_array($value, $allowedValues, true) ? $value : null;
    }
}
