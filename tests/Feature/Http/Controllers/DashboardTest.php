<?php

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can view their dashboard', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('dashboard displays user information', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertSee($user->name);
    $response->assertSee($user->email);
});
