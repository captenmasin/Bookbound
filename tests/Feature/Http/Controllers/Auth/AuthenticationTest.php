<?php

use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\assertAuthenticated;

describe('AuthenticationTest', function () {
    test('login screen can be rendered', function () {
        $response = get('/login');

        $response->assertStatus(200);
    });

    test('users can authenticate using the login screen', function () {
        $user = User::factory()->create();

        $response = post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        assertAuthenticated();

        $response->assertRedirect(route('dashboard', absolute: false));
    });

    test('users can authenticate using their username', function () {
        $user = User::factory()->create();

        $response = post('/login', [
            'login' => $user->username,
            'password' => 'password',
        ]);

        assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    });

    test('users can not authenticate with invalid password', function () {
        $user = User::factory()->create();

        post('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        assertGuest();
    });

    test('users can not authenticate with invalid username', function () {
        User::factory()->create([
            'username' => 'testuser',
        ]);

        post('/login', [
            'login' => 'wrongusername',
            'password' => 'password',
        ]);

        assertGuest();
    });

    test('users can logout', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->post('/logout');

        assertGuest();
        $response->assertRedirect('/');
    });
});
