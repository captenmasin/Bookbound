<?php

use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ConfirmablePasswordController', function () {
    it('renders the confirm password page for authenticated users', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->get(route('password.confirm'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('auth/ConfirmPassword')
            ->where('meta.title', 'Confirm your password | Bookbound')
            ->where('meta.description', 'Please confirm your password to continue.')
        );
    });

    it('redirects guest users to login page', function () {
        $response = get(route('password.confirm'));

        $response->assertRedirect(route('login', absolute: false));
    });

    it('confirms password and redirects to intended route', function () {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = actingAs($user)->post(route('password.confirm'), [
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('user.books.index', absolute: false));
        $response->assertSessionHas('auth.password_confirmed_at');
    });

    it('fails with invalid password', function () {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = actingAs($user)->post(route('password.confirm'), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['password']);
        $response->assertSessionMissing('auth.password_confirmed_at');
    });

    it('fails when password field is missing', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->post(route('password.confirm'), []);

        $response->assertSessionHasErrors(['password']);
        $response->assertSessionMissing('auth.password_confirmed_at');
    });

    it('sets password confirmation timestamp in session', function () {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $beforeTime = time();

        $response = actingAs($user)->post(route('password.confirm'), [
            'password' => 'password123',
        ]);

        $afterTime = time();
        $confirmedAt = $response->getSession()->get('auth.password_confirmed_at');

        expect($confirmedAt)->toBeGreaterThanOrEqual($beforeTime)
            ->and($confirmedAt)->toBeLessThanOrEqual($afterTime);
    });

    it('redirects to user books index by default after confirmation', function () {
        $user = User::factory()->create([
            'password' => Hash::make('test-password'),
        ]);

        $response = actingAs($user)->post(route('password.confirm'), [
            'password' => 'test-password',
        ]);

        $response->assertRedirect(route('user.books.index', absolute: false));
    });

    it('validates password against current user email', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('user-password'),
        ]);

        $response = actingAs($user)->post(route('password.confirm'), [
            'password' => 'user-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('auth.password_confirmed_at');
    });

    it('requires authenticated user for store method', function () {
        $response = post(route('password.confirm'), [
            'password' => 'some-password',
        ]);

        $response->assertRedirect(route('login', absolute: false));
    });

    it('handles special characters in password correctly', function () {
        $specialPassword = 'P@$$w0rd!#$%^&*()';
        $user = User::factory()->create([
            'password' => Hash::make($specialPassword),
        ]);

        $response = actingAs($user)->post(route('password.confirm'), [
            'password' => $specialPassword,
        ]);

        $response->assertRedirect(route('user.books.index', absolute: false));
        $response->assertSessionHas('auth.password_confirmed_at');
    });

    it('fails with empty string password', function () {
        $user = User::factory()->create([
            'password' => Hash::make('actual-password'),
        ]);

        $response = actingAs($user)->post(route('password.confirm'), [
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $response->assertSessionMissing('auth.password_confirmed_at');
    });

    it('uses correct validation message for invalid password', function () {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = actingAs($user)->post(route('password.confirm'), [
            'password' => 'wrong-password',
        ]);

        $errors = $response->getSession()->get('errors');
        expect($errors->get('password'))->toContain(__('auth.password'));
    });

    it('honors intended redirect when no specific redirect is set', function () {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = actingAs($user)->post(route('password.confirm'), [
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.books.index', absolute: false));
    });
});
