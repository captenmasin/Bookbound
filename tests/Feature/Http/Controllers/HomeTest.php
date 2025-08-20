<?php

it('has home page', function () {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->component('Home')
        );
});
