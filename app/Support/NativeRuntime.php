<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class NativeRuntime
{
    public static function isNative(): bool
    {
        return (bool) config('nativephp-internal.running');
    }

    public static function nativePlatform(): ?string
    {
        return config('nativephp-internal.platform');
    }

    public static function isPwa(Request $request): bool
    {
        return $request->boolean('pwa-mode') || Cookie::get('pwa-mode') === 'true';
    }

    public static function pwaPlatform(Request $request): ?string
    {
        $requestPlatform = $request->input('pwa-device');
        $cookiePlatform = Cookie::get('pwa-device');

        $platform = is_string($requestPlatform) && $requestPlatform !== ''
            ? $requestPlatform
            : $cookiePlatform;

        return in_array($platform, ['android', 'ios', 'macos'], true) ? $platform : null;
    }

    public static function isStandalone(Request $request): bool
    {
        return self::isNative() || self::isPwa($request);
    }

    public static function platform(Request $request): ?string
    {
        return self::nativePlatform() ?? self::pwaPlatform($request);
    }

    /**
     * @return array<string, bool>
     */
    public static function capabilities(): array
    {
        if (! self::isNative()) {
            return [
                'scanner' => false,
                'share' => false,
                'device' => false,
                'system' => false,
                'secure_storage' => false,
                'push_notifications' => false,
            ];
        }

        return [
            'scanner' => true,
            'share' => true,
            'device' => true,
            'system' => true,
            'secure_storage' => true,
            'push_notifications' => true,
        ];
    }
}
