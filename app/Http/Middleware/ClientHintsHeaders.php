<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientHintsHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add on real HTML top-level navigations (avoid assets, JSON, etc.)
        if (! config('services.pirsch.enabled')) {
            return $response;
        }
        if (! $request->isSecure()) { // CH are HTTPS-only
            return $response;
        }
        $ct = $response->headers->get('Content-Type', '');
        if (stripos($ct, 'text/html') === false) {
            return $response;
        }

        // 1) Ask the browser to send these Client Hints on subsequent requests
        //    (and persist them for this origin).
        $response->headers->set('Accept-CH',
            'Sec-CH-UA, Sec-CH-UA-Mobile, Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version, '.
            'Sec-CH-Width, Sec-CH-Viewport-Width, Width, Viewport-Width'
        );

        // 2) Delegate those hints to Pirsch (and self) via Permissions-Policy.
        //    Include width + viewport-width to match Accept-CH and silence warnings.
        $policy = implode(', ', [
            'ch-ua=(self "https://api.pirsch.io")',
            'ch-ua-mobile=(self "https://api.pirsch.io")',
            'ch-ua-platform=(self "https://api.pirsch.io")',
            'ch-ua-platform-version=(self "https://api.pirsch.io")',
            'ch-width=(self "https://api.pirsch.io")',
            'ch-viewport-width=(self "https://api.pirsch.io")',
            'width=(self "https://api.pirsch.io")',
            'viewport-width=(self "https://api.pirsch.io")',
        ]);
        $response->headers->set('Permissions-Policy', $policy);

        // Optional but nice: tell caches that responses may vary on CH
        $response->headers->set('Vary', trim($response->headers->get('Vary').', Sec-CH-UA, Sec-CH-UA-Platform, Sec-CH-UA-Mobile, Sec-CH-Viewport-Width, Sec-CH-Width'), ', ');

        return $response;
    }
}
