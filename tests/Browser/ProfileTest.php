<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

// Users can update their profile information
test('user can update basic profile information', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'username' => 'olduser',
        'email' => 'old@example.com',
    ]);

    actingAs($user);

    visit('/settings/profile')
        ->assertSee('Full name')
        ->type('#name', 'New Name')
        ->type('#username', 'newuser')
        ->type('#email', 'new@example.com')
        ->press('Save')
        ->assertSee('Profile updated successfully')
        ->assertSee('Your email address is unverified');

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->username)->toBe('newuser')
        ->and($user->email)->toBe('new@example.com');
});

test('username must be unique', function () {
    User::factory()->create(['username' => 'takenuser']);
    $user = User::factory()->create(['username' => 'originaluser']);

    actingAs($user);

    visit('/settings/profile')
        ->type('#username', 'takenuser')
        ->press('Save')
        ->assertSee('The username has already been taken');
});

test('avatar must be an image', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/settings/profile')
        ->assertSee('Avatar')
        ->attach('#avatar', __DIR__.'/fixtures/text-file.txt')
        ->press('Save')
        ->assertSee('The avatar field must be an image');
})->todo('fix this test');

test('user can remove avatar', function () {
    $user = User::factory()->create();

    $avatarFile = __DIR__.'/fixtures/avatar.png';

    copy($avatarFile, __DIR__.'/fixtures/avatar_copy.png');

    $user->addMedia(__DIR__.'/fixtures/avatar_copy.png')
        ->toMediaCollection('avatar');

    actingAs($user);

    visit('/settings/profile')
        ->press('Remove avatar');

    $user->refresh();
    expect($user->avatar)->toBe('');
})->todo('fix this test');

test('user can request a new verification email', function () {
    $user = User::factory()->create([
        'email' => 'old@example.com',
    ]);

    actingAs($user);

    visit('/settings/profile')
        ->type('#email', 'new@example.com')
        ->press('Save')
        ->assertSee('Your email address is unverified')
        ->press('Click here to resend the verification email.')
        ->assertSee('A new verification link has been sent to your email address.');

    $user->refresh();

    expect($user->email)->toBe('new@example.com')
        ->and($user->hasVerifiedEmail())->toBeFalse();
});

test('user can update avatar', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/settings/profile')
        ->attach('#avatar', __DIR__.'/fixtures/avatar.png')
        ->press('Save')
        ->assertSee('Profile updated successfully');

    $user->refresh();

    expect($user->avatar)->not->toBe('');
})->todo('fix this test');
