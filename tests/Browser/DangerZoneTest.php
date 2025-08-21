<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

test('user can delete their account with the correct password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('delete-me'),
    ]);

    actingAs($user);

    visit('/settings/danger')
        ->assertSee('Delete account')
        ->press('#delete-account-trigger')
        ->assertSee('Are you sure you want to delete your account?')
        ->type('#password', 'delete-me')
        ->click('#confirm-delete-account')
        ->assertPathIs('/login');

    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('user sees error if password is incorrect', function () {
    $user = User::factory()->create([
        'password' => bcrypt('correct-password'),
    ]);

    actingAs($user);

    visit('/settings/danger')
        ->assertSee('Delete account')
        ->press('#delete-account-trigger')
        ->assertSee('Are you sure you want to delete your account?')
        ->type('#password', 'delete-me')
        ->click('#confirm-delete-account')
        ->assertSee('The password is incorrect');

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('user can cancel account deletion', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/settings/danger')
        ->assertSee('Delete account')
        ->press('#delete-account-trigger')
        ->type('#password', 'anything')
        ->press('Cancel')
        ->assertMissing('[role="dialog"]');

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});
