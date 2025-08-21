<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

// Guest can view the marketing homepage and see key sections

test('guest sees marketing sections on home page', function () {
    visit('/')
        ->assertSee('How it works')
        ->assertSee('Why You’ll Love It')
        ->assertSee('Plans & Pricing')
        ->assertSee('Frequently asked questions')
        ->assertSee('Log in')
        ->assertSee('Get Started Free');
});

test('authenticated user sees library button on home page', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/')->assertSee('Your Library');
});
