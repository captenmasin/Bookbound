<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Actions\TrackEvent;
use Illuminate\Http\Request;
use App\Enums\AnalyticsEvent;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;

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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|alpha_dash|unique:'.User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $user->assignRole('user');

        TrackEvent::dispatchAfterResponse(AnalyticsEvent::UserAccountCreated, [
            'user_id' => $user->id,
        ]);

        Auth::login($user);

        return to_route('user.books.index');
    }
}
