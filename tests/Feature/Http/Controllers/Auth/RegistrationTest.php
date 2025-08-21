<?php

use Inertia\Testing\AssertableInertia;

describe('Registration', function () {
    test('registration screen can be rendered', function () {
        $response = $this->get('/register');

        $response->assertStatus(200);
    });

    test('new users can register', function () {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => \Illuminate\Support\Str::random(),
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        $response->assertRedirect(route('user.books.index', absolute: false));

        $this->followRedirects($response)->assertInertia(fn (AssertableInertia $page) => $page
            ->component('auth/VerifyEmail')
        );
    });

    test('username field is required during registration', function () {
        $response = $this->post('/register', [
            // 'username' => '', // omit username
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
    });

    test('username field must be valid', function () {
        $response = $this->post('/register', [
            'username' => 'name with space',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
    });
});
