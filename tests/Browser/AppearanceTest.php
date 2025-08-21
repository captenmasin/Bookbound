<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

test('user can toggle book tilting setting', function () {
    $user = User::factory()->create([
        'settings' => [
            'library' => [
                'tilt_books' => false,
            ],
        ],
    ]);

    actingAs($user);
    visit('/settings/appearance')
        ->assertAriaAttribute('#library-tilt', 'checked', 'false') // off by default
        ->press('#library-tilt') // toggle on
        ->assertAriaAttribute('#library-tilt', 'checked', 'true')
        ->press('#library-tilt')
        ->wait(1);

    $user->refresh();

    $userSettings = $user->settings ? json_decode($user->settings) : [];

    expect(data_get($userSettings, 'library.tilt_books'))->toBeFalse();
});

test('user can switch between light, dark, and system themes', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/settings/appearance')
        ->press('Dark')
        ->assertScript("document.cookie.includes('appearance=dark')")
        ->press('Light')
        ->assertScript("document.cookie.includes('appearance=light')")
        ->press('System')
        ->assertScript("document.cookie.includes('appearance=system')");
});
