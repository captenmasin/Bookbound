<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

test('user must be logged in to view dashboard', function () {
    visit('/books')->assertPathIs('/login');
});

test('user can see dashboard', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/books')->assertSee('Your Library');
});
