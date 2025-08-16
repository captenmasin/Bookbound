<?php

use App\Models\User;
use Laravel\Dusk\Browser;

// Guest can view the marketing homepage and see key sections

test('guest sees marketing sections on home page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('How it works')
            ->assertSee('Why You’ll Love It')
            ->assertSee('Plans & Pricing')
            ->assertSee('Frequently asked questions')
            // Header actions
            ->assertSee('Log in')
            ->assertSee('Get Started Free');
    });
});

// Authenticated users see the library CTA instead of the signup CTA

test('authenticated user sees library button on home page', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/')
            ->assertSee('Your Library');
    });
});
