<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

test('user can change their password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    actingAs($user);

    visit('/settings/password')
        ->type('#password', 'new-secure-password')
        ->type('#password_confirmation', 'new-secure-password')
        ->type('#current_password', 'old-password')
        ->press('Save password')
        ->assertSee('Password updated successfully');

    $this->assertTrue(auth()->attempt([
        'email' => $user->email,
        'password' => 'new-secure-password',
    ]));
});

test('password confirmation must match', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret'),
    ]);

    actingAs($user);

    visit('/settings/password')
        ->type('#password', 'newpass123')
        ->type('#password_confirmation', 'differentpass123')
        ->type('#current_password', 'secret')
        ->press('Save password')
        ->assertSee('The password field confirmation does not match');
});

test('current password must be correct', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret'),
    ]);

    actingAs($user);

    visit('/settings/password')
        ->type('#password', 'newpass123')
        ->type('#password_confirmation', 'newpass123')
        ->type('#current_password', 'wrongpassword')
        ->press('Save password')
        ->assertSee('The password is incorrect');
});

test('password must meet validation rules', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret'),
    ]);

    actingAs($user);

    visit('/settings/password')
        ->type('#password', 'short')
        ->type('#password_confirmation', 'short')
        ->type('#current_password', 'secret')
        ->press('Save password')
        ->assertSee('The password field must be at least 8 characters.');
});

test('form resets after successful password change', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpass'),
    ]);

    actingAs($user);

    visit('/settings/password')
        ->type('#password', 'newpass123')
        ->type('#password_confirmation', 'newpass123')
        ->type('#current_password', 'oldpass')
        ->press('Save password')
        ->assertSee('Password updated successfully')
        ->assertValue('#password', '')
        ->assertValue('#password_confirmation', '')
        ->assertValue('#current_password', '');
});
