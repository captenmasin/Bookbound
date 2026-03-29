<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Horizon\Horizon;
use App\Enums\UserPermission;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->authorization();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Configure the Horizon authorization services.
     */
    protected function authorization(): void
    {
        $this->gate();

        Horizon::auth(function (Request $request): bool {
            return Gate::check('viewHorizon', [$request->user()]) || app()->environment('local');
        });
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user = null): bool {
            if (request()->bearerToken() && request()->bearerToken() === config('services.horizon.token')) {
                return true;
            }

            return $user?->can(UserPermission::VIEW_HORIZON_PANEL) ?? false;
        });
    }
}
