<?php

use App\Models\User;
use Laravel\Dusk\Browser;

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
        ->press('#library-tilt');

    $user->refresh();

    $userSettings = $user->settings ? json_decode($user->settings) : [];

    expect(data_get($userSettings, 'library.tilt_books'))->toBeFalse();
});
//
// test('user can switch between light, dark, and system themes', function () {
//    $user = User::factory()->create();
//
//    $this->browse(function (Browser $browser) use ($user) {
//        $browser->loginAs($user)
//            ->visit('/settings/appearance')
//            ->pause(500)
//            ->press('Dark')
//            ->assertPlainCookieValue('appearance', 'dark')
//            ->pause(300)
//            ->press('Light')
//            ->assertPlainCookieValue('appearance', 'light')
//            ->pause(300)
//            ->press('System')
//            ->assertPlainCookieValue('appearance', 'system')
//            ->pause(300);
//        //            ->refresh()
//        //            ->assertAttributeContains('@html', 'class', 'dark');
//    });
// });
