<?php

use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\assertAuthenticated;

it('renders the login page with expected props and meta', function () {
    $response = get(route('login'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('auth/Login')
        ->where('canResetPassword', true)
        ->where('redirect', null)
        ->where('meta.title', 'Log in to your account | Bookbound')
        ->where('meta.description', 'Enter your email/username and password below to log in.')
    );
});

it('passes through a valid redirect query to the login page', function () {
    $redirect = 'https://example.com/return-here';

    $response = get(route('login', ['redirect' => $redirect], absolute: false));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('auth/Login')
        ->where('redirect', $redirect)
    );
});

it('authenticates and redirects to dashboard by default', function () {
    $user = User::factory()->create();

    $response = post(route('login', absolute: false), [
        'login' => $user->email,
        'password' => 'password',
    ]);

    assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

it('authenticates and honors a valid absolute redirect URL', function () {
    $user = User::factory()->create();

    $redirect = 'https://example.com/after-login';

    $response = post(route('login', absolute: false), [
        'login' => $user->email,
        'password' => 'password',
        'redirect' => $redirect,
    ]);

    assertAuthenticated();
    $response->assertRedirect($redirect);
});

it('rejects an invalid redirect URL and does not authenticate', function () {
    User::factory()->create(['email' => 'john@example.com']);

    // Not a valid absolute URL, so it should fail validation per LoginRequest rules
    $response = post(route('login', absolute: false), [
        'login' => 'john@example.com',
        'password' => 'password',
        'redirect' => '/not-absolute',
    ]);

    assertGuest();
    $response->assertSessionHasErrors('redirect');
});
