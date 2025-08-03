<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PwaDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->boolean('pwa-mode') && in_array($request->input('pwa-device'), ['ios', 'android', 'macos'])) {
            // Set the PWA device cookie
            $response->headers->setCookie(cookie('pwa-mode', 'true', 60 * 24 * 365, '/', null, false, false));
            $response->headers->setCookie(cookie('pwa-device', $request->input('pwa-device'), 60 * 24 * 365, '/', null, false, false));
        } else {
            // Remove the PWA device cookie if it exists
            if ($request->hasCookie('pwa-mode') || $request->hasCookie('pwa-device')) {
                $response->headers->setCookie(cookie('pwa-mode', '', -1, '/', null, false, false));
                $response->headers->setCookie(cookie('pwa-device', '', -1, '/', null, false, false));
            }
        }

        return $response;
    }
}
