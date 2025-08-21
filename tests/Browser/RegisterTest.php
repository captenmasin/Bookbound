<?php

use App\Models\User;

test('user can register and be redirected to verify emails', function () {
    $password = Str::random(16);

    visit('/register')
        ->type('#name', 'Test User')
        ->type('#username', 'testuser')
        ->type('#email', 'testuser@example.com')
        ->type('#password', $password)
        ->type('#password_confirmation', $password)
        ->press('Create account')
        ->assertPathIs('/verify-email')
        ->assertSee('Verify email');
});

test('user cannot register with mismatched passwords', function () {
    $password = Str::random(16);

    visit('/register')
        ->type('#name', 'Mismatch User')
        ->type('#username', 'mismatch')
        ->type('#email', 'mismatch@example.com')
        ->type('#password', $password)
        ->type('#password_confirmation', 'wrongpass')
        ->press('Create account')
        ->assertSee('The password field confirmation does not match.');
});

test('user cannot register without username', function () {
    $password = Str::random(16);

    visit('/register')
        ->type('#name', 'Mismatch User')
        ->type('#email', 'mismatch@example.com')
        ->type('#password', $password)
        ->type('#password_confirmation', $password)
        ->press('Create account')
        ->assertSee('The username field is required.');
});

test('email must be unique', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $password = Str::random(16);
    visit('/register')
        ->type('#name', 'Duplicate User')
        ->type('#username', \Illuminate\Support\Str::random())
        ->type('#email', 'existing@example.com')
        ->type('#password', $password)
        ->type('#password_confirmation', $password)
        ->press('Create account')
        ->assertSee('The email has already been taken');
});

test('username must be unique', function () {
    User::factory()->create(['username' => 'existinguser']);

    $password = Str::random(16);
    visit('/register')
        ->type('#name', 'Duplicate User')
        ->type('#username', 'existinguser')
        ->type('#email', 'existing@example.com')
        ->type('#password', $password)
        ->type('#password_confirmation', $password)
        ->press('Create account')
        ->assertSee('The username has already been taken.');
});
