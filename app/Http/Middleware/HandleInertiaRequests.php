<?php

namespace App\Http\Middleware;

use Inertia\Inertia;
use Inertia\Middleware;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $currentUser = Auth::check()
            ? (new UserResource($request->user()->load('books')))->asUser()
            : null;

        $backUrl = null;
        if ($request->filled('src') && Str::startsWith($request->get('src'), config('app.url'))) {
            $backUrl = $request->get('src');
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'domain' => parse_url(config('app.url'), PHP_URL_HOST),
                'route' => $request->route()?->getName(),
                'storage_url' => config('filesystems.disks.public.url'),
            ],
            'currentUrl' => url()->full(),
            'currentPath' => request()->path(),
            'auth' => fn () => [
                'user' => $currentUser,
                'check' => Auth::check(),
            ],

            'backUrl' => $backUrl,

            'flash' => Inertia::always(fn () => [
                'info' => fn () => $request->hasSession() ? $request->session()->get('info') : null,
                'success' => fn () => $request->hasSession() ? $request->session()->get('success') : null,
                'error' => fn () => $request->hasSession() ? $request->session()->get('error') : null,
            ]),

            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
