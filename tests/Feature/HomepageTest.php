<?php

test('guests are redirected to the login page', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login');
});

test('authenticated users can view their homepage', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/');
    $response->assertStatus(200);
});
