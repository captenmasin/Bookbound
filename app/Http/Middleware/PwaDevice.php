<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\PwaMode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PwaDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $explicitPwaMode = PwaMode::explicit($request);
        $pwaDevice = PwaMode::deviceSignal($request);

        if ($explicitPwaMode === true && $pwaDevice !== null) {
            $response->headers->setCookie(cookie('pwa-mode', 'true', 60 * 24 * 365, '/', null, false, false));
            $response->headers->setCookie(cookie('pwa-device', $pwaDevice, 60 * 24 * 365, '/', null, false, false));
        } elseif ($explicitPwaMode === false) {
            $response->headers->setCookie(cookie('pwa-mode', '', -1, '/', null, false, false));
            $response->headers->setCookie(cookie('pwa-device', '', -1, '/', null, false, false));
        }

        return $response;
    }
}
