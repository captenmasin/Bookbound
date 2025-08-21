<?php

use App\Models\User;

test('user can login and be redirected to books', function () {
    $password = Str::password();

    $user = User::factory()->create([
        'password' => $password,
    ]);

    visit('/login')
        ->assertSee('Log in')
        ->type('#login', $user->email) // email OR username
        ->type('#password', $password)
        ->press('Log in')
        ->assertPathIs('/dashboard')
        ->assertSee('Welcome');
});

test('user can login using username', function () {
    $password = Str::password();

    User::factory()->create([
        'username' => 'testuser123',
        'password' => $password,
    ]);

    visit('/login')
        ->assertSee('Log in')
        ->type('#login', 'testuser123')
        ->type('#password', $password)
        ->press('Log in')
        ->assertPathIs('/dashboard')
        ->assertSee('Welcome');
});

test('user cannot login with invalid credentials', function () {
    $password = Str::password();
    $wrongPassword = Str::password();

    $user = User::factory()->create([
        'password' => $password,
    ]);

    visit('/login')
        ->assertSee('Log in')
        ->type('#login', $user->email)
        ->type('#password', $wrongPassword)
        ->pressAndWaitFor('Log in')
        ->assertSee('These credentials do not match our records.');

    visit('/login')
        ->assertSee('Log in')
        ->type('#login', 'wrongemail@example.com')
        ->type('#password', $wrongPassword)
        ->pressAndWaitFor('Log in')
        ->assertSee('These credentials do not match our records.');
});

test('login and password are required', function () {
    visit('/login')
        ->press('Log in')
        ->assertSee('The login field is required.')
        ->assertSee('The password field is required.');

    visit('/login')
        ->press('Log in')
        ->type('#login', 'someuser')
        ->assertSee('The password field is required.');
});
