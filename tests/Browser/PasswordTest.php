<?php

use App\Models\User;
use Laravel\Dusk\Browser;

test('user can change their password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/settings/password')
            ->type('#password', 'new-secure-password')
            ->type('#password_confirmation', 'new-secure-password')
            ->type('#current_password', 'old-password')
            ->press('Save password')
            ->waitForText('Password updated successfully');
    });

    $this->assertTrue(auth()->attempt([
        'email' => $user->email,
        'password' => 'new-secure-password',
    ]));
});

test('password confirmation must match', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/settings/password')
            ->type('#password', 'newpass123')
            ->type('#password_confirmation', 'differentpass123')
            ->type('#current_password', 'secret')
            ->press('Save password')
            ->waitForText('The password field confirmation does not match')
            ->assertSee('The password field confirmation does not match');
    });
});

test('current password must be correct', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/settings/password')
            ->type('#password', 'newpass123')
            ->type('#password_confirmation', 'newpass123')
            ->type('#current_password', 'wrongpassword')
            ->press('Save password')
            ->waitForText('The password is incorrect')
            ->assertSee('The password is incorrect');
    });
});

test('password must meet validation rules', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/settings/password')
            ->type('#password', 'short')
            ->type('#password_confirmation', 'short')
            ->type('#current_password', 'secret')
            ->press('Save password')
            ->waitForText('The password field must be at least 8 characters.')
            ->assertSee('The password field must be at least 8 characters.');
    });
});

test('form resets after successful password change', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpass'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/settings/password')
            ->type('#password', 'newpass123')
            ->type('#password_confirmation', 'newpass123')
            ->type('#current_password', 'oldpass')
            ->press('Save password')
            ->waitForText('Password updated successfully')
            ->assertInputValue('#password', '')
            ->assertInputValue('#password_confirmation', '')
            ->assertInputValue('#current_password', '');
    });
});
