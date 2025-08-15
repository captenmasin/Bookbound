<?php

use App\Models\User;
use Laravel\Dusk\Browser;

test('user can login and be redirected to books', function () {
    $this->browse(function (Browser $browser) {
        $password = Str::password();

        $user = User::factory()->create([
            'password' => $password,
        ]);

        $browser->fullLogout();
        $browser->visit('/login')
            ->assertSee('Log in')
            ->type('#login', $user->email) // email OR username
            ->type('#password', $password)
            ->press('Log in')
            ->waitForLocation('/dashboard')
            ->assertPathIs('/dashboard')
            ->assertSee('Welcome')
            ->fullLogout();
    });
});

test('user can login using username', function () {
    $this->browse(function (Browser $browser) {
        $password = Str::password();

        User::factory()->create([
            'username' => 'testuser123',
            'password' => $password,
        ]);

        $browser->visit('/login')
            ->assertSee('Log in')
            ->type('#login', 'testuser123')
            ->type('#password', $password)
            ->press('Log in')
            ->waitForLocation('/dashboard')
            ->assertPathIs('/dashboard')
            ->assertSee('Welcome')
            ->fullLogout();
    });
});

test('user cannot login with invalid credentials', function () {
    $this->browse(function (Browser $browser) {
        $password = Str::password();
        $wrongPassword = Str::password();

        $user = User::factory()->create([
            'password' => $password,
        ]);

        $browser->visit('/login')
            ->assertSee('Log in')
            ->waitFor('#login')
            ->type('#login', $user->email)
            ->type('#password', $wrongPassword)
            ->pressAndWaitFor('Log in')
            ->waitForText('These credentials do not match our records.', 5)
            ->assertSee('These credentials do not match our records.');

        $browser->visit('/login')
            ->assertSee('Log in')
            ->waitFor('#login')
            ->type('#login', 'wrongemail@example.com')
            ->type('#password', $wrongPassword)
            ->pressAndWaitFor('Log in')
            ->assertSee('These credentials do not match our records.');
    });
});

test('login and password are required', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->press('Log in')
            ->waitForText('The login field is required', 5)
            ->assertSee('The login field is required.')
            ->assertSee('The password field is required.');

        $browser->visit('/login')
            ->press('Log in')
            ->type('#login', 'someuser')
            ->waitForText('The password field is required.', 5)
            ->assertSee('The password field is required.');
    });
});
