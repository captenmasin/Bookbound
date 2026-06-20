<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Enums\UserRole;
use App\Support\Turnstile;
use App\Actions\TrackEvent;
use Illuminate\Http\Request;
use App\Enums\AnalyticsEvent;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register')
            ->withMeta([
                'title' => 'Create an account',
                'description' => 'Enter your details below to create your account.',
            ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|alpha_dash|unique:'.User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if (config('services.turnstile.enabled')) {
            $rules['cf_response'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        if (config('services.turnstile.enabled')) {
            $turnstile = new Turnstile;
            $response = $turnstile->validate($validated['cf_response']);

            if ($response['status'] === 0) {
                throw ValidationException::withMessages(['cf_response' => 'Captcha failed']);
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        TrackEvent::dispatchAfterResponse(AnalyticsEvent::UserAccountCreated, [
            'user_id' => $user->id,
        ]);

        $user->assignRole(Role::where('name', UserRole::User->value)->first());

        Auth::login($user);

        return to_route('user.books.index');
    }
}
