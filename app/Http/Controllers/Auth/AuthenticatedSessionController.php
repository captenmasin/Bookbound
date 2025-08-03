<?php

namespace App\Http\Controllers\Auth;

use Uri;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        $redirectUrl = $request->input('redirect', null);

        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'redirect' => $redirectUrl,
            'status' => $request->session()->get('status'),
        ])->withMeta([
            'title' => 'Log in to your account',
            'description' => 'Enter your email/username and password below to log in.',
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->boolean('pwa-mode') && $request->has('pwa-device')) {
            $intendedUrl = session()->pull('url.intended');
            $intendedUrl = Uri::of($intendedUrl)
                ->withQuery([
                    'pwa-mode' => 'true',
                    'pwa-device' => $request->input('pwa-device'),
                ]);

            session()->put('url.intended', $intendedUrl);

            $defaultRedirect = Uri::of(route('home'))
                ->withQuery([
                    'pwa-mode' => 'true',
                    'pwa-device' => $request->input('pwa-device'),
                ]);
        }

        if ($request->has('redirect')) {
            $redirectUrl = $request->input('redirect');
            if ($request->boolean('pwa-mode') && $request->has('pwa-device')) {
                $redirectUrl = Uri::of($redirectUrl)
                    ->withQuery([
                        'pwa-mode' => 'true',
                        'pwa-device' => $request->input('pwa-device'),
                    ]);
            }

            if (filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                return redirect()->to($redirectUrl);
            }
        }

        return redirect()->intended($defaultRedirect);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
