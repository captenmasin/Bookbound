<?php

namespace App\Support;

use Illuminate\Http\Request;

class PwaMode
{
    public static function resolve(Request $request): bool
    {
        return self::explicit($request)
            ?? self::fromCookie($request)
            ?? false;
    }

    public static function explicit(Request $request): ?bool
    {
        return self::fromHeader($request)
            ?? self::fromQuery($request)
            ?? self::fromUserAgent($request);
    }

    public static function fromHeader(Request $request): ?bool
    {
        if (! $request->headers->has('X-PWA-Mode')) {
            return null;
        }

        return self::normalizeBoolean($request->headers->get('X-PWA-Mode'));
    }

    public static function fromQuery(Request $request): ?bool
    {
        if (! $request->query->has('pwa-mode')) {
            return null;
        }

        return self::normalizeBoolean($request->query('pwa-mode'));
    }

    public static function fromCookie(Request $request): ?bool
    {
        if (! $request->cookies->has('pwa-mode')) {
            return null;
        }

        return self::normalizeBoolean($request->cookie('pwa-mode'));
    }

    public static function deviceSignal(Request $request): ?string
    {
        $device = $request->headers->get('X-PWA-Device') ?? $request->input('pwa-device');

        if (is_string($device)) {
            return self::normalizeDevice($device);
        }

        return self::deviceFromUserAgent($request);
    }

    public static function resolveDevice(Request $request): ?string
    {
        return self::deviceSignal($request)
            ?? self::fromCookieDevice($request);
    }

    private static function normalizeBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return match ($value) {
                1 => true,
                0 => false,
                default => null,
            };
        }

        if (! is_string($value)) {
            return null;
        }

        return match (strtolower(trim($value))) {
            '1', 'true', 'on', 'yes' => true,
            '0', 'false', 'off', 'no' => false,
            default => null,
        };
    }

    private static function fromCookieDevice(Request $request): ?string
    {
        if (! $request->cookies->has('pwa-device')) {
            return null;
        }

        return self::normalizeDevice($request->cookie('pwa-device'));
    }

    private static function fromUserAgent(Request $request): ?bool
    {
        $userAgent = self::normalizedUserAgent($request);

        if ($userAgent === null) {
            return null;
        }

        if (preg_match('/\bpwa(?:[-_\s]?mode)?\b\s*[:=\/]\s*([a-z0-9]+)/i', $userAgent, $matches) === 1) {
            return self::normalizeBoolean($matches[1]);
        }

        if (preg_match('/\b(?:standalone|pwa)\b/i', $userAgent) === 1) {
            return true;
        }

        if (self::looksLikeIosStandaloneUserAgent($userAgent)) {
            return true;
        }

        return null;
    }

    private static function deviceFromUserAgent(Request $request): ?string
    {
        $userAgent = self::normalizedUserAgent($request);

        if ($userAgent === null) {
            return null;
        }

        if (preg_match('/\bpwa[-_\s]?device\b\s*[:=\/]\s*([a-z0-9\s_-]+)/i', $userAgent, $matches) === 1) {
            return self::normalizeDevice($matches[1]);
        }

        return match (true) {
            preg_match('/\b(?:iphone|ipad|ipod|ios)\b/', $userAgent) === 1 => 'ios',
            preg_match('/\bandroid\b/', $userAgent) === 1 => 'android',
            preg_match('/\b(?:macos|mac os|macintosh)\b/', $userAgent) === 1 => 'macos',
            default => null,
        };
    }

    private static function normalizedUserAgent(Request $request): ?string
    {
        $userAgent = $request->userAgent();

        if (! is_string($userAgent) || trim($userAgent) === '') {
            return null;
        }

        return strtolower(trim($userAgent));
    }

    private static function looksLikeIosStandaloneUserAgent(string $userAgent): bool
    {
        $isIosDevice = preg_match('/\b(?:iphone|ipad|ipod)\b/', $userAgent) === 1;
        $isWebKitMobile = preg_match('/applewebkit.+mobile\//', $userAgent) === 1;
        $hasSafariShell = preg_match('/\b(?:safari|crios|fxios|edgios|opios)\//', $userAgent) === 1;

        return $isIosDevice && $isWebKitMobile && ! $hasSafariShell;
    }

    private static function normalizeDevice(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $device = strtolower(trim($value));

        if (! in_array($device, ['ios', 'android', 'macos'], true)) {
            return null;
        }

        return $device;
    }
}
