<?php

use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\Hash;
use Spatie\LaravelPasskeys\Models\Passkey;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Settings\PasswordController', function () {
    it('renders the password settings page for authenticated users', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->get(route('user.settings.password.edit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('settings/Password')
            ->has('passkeys')
            ->where('meta.title', 'Password Settings | Bookbound')
            ->where('meta.description', 'Manage your password and passkeys.')
        );
    });

    it('includes user passkeys in the response', function () {
        $user = User::factory()->create();

        // Create a mock passkey
        $passkey = Passkey::factory()->create([
            'authenticatable_id' => $user->id,
            'name' => 'Test Passkey',
            'last_used_at' => now(),
        ]);

        $response = actingAs($user)->get(route('user.settings.password.edit'));

        $response->assertInertia(fn ($page) => $page
            ->has('passkeys', 1)
            ->where('passkeys.0.id', $passkey->id)
            ->where('passkeys.0.name', 'Test Passkey')
            ->has('passkeys.0.last_used_at')
        );
    });

    it('redirects guest users to login page', function () {
        $response = get(route('user.settings.password.edit'));

        $response->assertRedirect(route('login', absolute: false));
    });

    it('successfully updates user password', function () {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = actingAs($user)->put(route('user.settings.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-secure-password123',
            'password_confirmation' => 'new-secure-password123',
        ]);

        $response->assertRedirect();

        expect(Hash::check('new-secure-password123', $user->fresh()->password))->toBeTrue();
    });

    it('fails to update password with incorrect current password', function () {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = actingAs($user)->put(route('user.settings.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        expect(Hash::check('correct-password', $user->fresh()->password))->toBeTrue();
    });

    it('fails to update password when current password is missing', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->put(route('user.settings.password.update'), [
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    });

    it('fails to update password when new password is missing', function () {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $response = actingAs($user)->put(route('user.settings.password.update'), [
            'current_password' => 'current-password',
        ]);

        $response->assertSessionHasErrors(['password']);
    });

    it('fails to update password when password confirmation does not match', function () {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $response = actingAs($user)->put(route('user.settings.password.update'), [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['password']);
        expect(Hash::check('current-password', $user->fresh()->password))->toBeTrue();
    });

    it('fails to update password when new password is too weak', function () {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $response = actingAs($user)->put(route('user.settings.password.update'), [
            'current_password' => 'current-password',
            'password' => '123', // Too short
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors(['password']);
    });

    it('requires authentication for password update', function () {
        $response = put(route('user.settings.password.update'), [
            'current_password' => 'some-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertRedirect(route('login', absolute: false));
    });

    it('validates passkey store request', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->post(route('profile.passkeys.store'), []);

        $response->assertSessionHasErrors(['passkey', 'options']);
    });

    it('validates passkey store request with invalid JSON', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->post(route('profile.passkeys.store'), [
            'passkey' => 'invalid-json',
            'options' => 'invalid-json',
        ]);

        $response->assertSessionHasErrors(['passkey', 'options']);
    });

    it('successfully stores a passkey', function () {
        $user = User::factory()->create();

        $validPasskeyData = json_encode([
            'id' => 'test-id',
            'rawId' => 'test-raw-id',
            'response' => [
                'clientDataJSON' => 'test-client-data',
                'attestationObject' => 'test-attestation',
            ],
            'type' => 'public-key',
        ]);

        $validOptionsData = json_encode([
            'challenge' => 'test-challenge',
            'rp' => ['name' => 'Test App'],
            'user' => [
                'id' => 'test-user-id',
                'name' => $user->email,
                'displayName' => $user->name,
            ],
        ]);

        actingAs($user)->get(route('user.settings.password.edit'));

        $response = actingAs($user)->post(route('profile.passkeys.store'), [
            'passkey' => $validPasskeyData,
            'options' => $validOptionsData,
        ]);

        $response->assertRedirectBack();
    });

    it('handles passkey store exceptions gracefully', function () {
        $user = User::factory()->create();

        // Use invalid data that will cause the StorePasskeyAction to throw an exception
        $invalidPasskeyData = json_encode([
            'id' => '',
            'rawId' => '',
            'response' => [],
            'type' => 'invalid',
        ]);

        $invalidOptionsData = json_encode([
            'challenge' => '',
            'rp' => [],
            'user' => [],
        ]);

        $response = actingAs($user)->post(route('profile.passkeys.store'), [
            'passkey' => $invalidPasskeyData,
            'options' => $invalidOptionsData,
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('successfully deletes user passkey', function () {
        $user = User::factory()->create();
        $passkey = Passkey::factory()->create([
            'authenticatable_id' => $user->id,
            'name' => 'Test Passkey',
        ]);

        $response = actingAs($user)->delete(route('profile.passkeys.delete', $passkey->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Passkey deleted successfully');

        expect(Passkey::find($passkey->id))->toBeNull();
    });

    it('only allows users to delete their own passkeys', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $passkey = Passkey::factory()->create([
            'authenticatable_id' => $user2->id,
            'name' => 'User 2 Passkey',
        ]);

        $response = actingAs($user1)->delete(route('profile.passkeys.delete', $passkey->id));

        $response->assertRedirect();
        expect(Passkey::find($passkey->id))->not->toBeNull(); // Should still exist
    });

    it('generates passkey options for authenticated users', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->get(route('profile.passkeys.generate-options'));

        $response->assertOk();
        // The response should be JSON with passkey options
        $response->assertJson([]);
    });

    it('requires authentication for passkey options generation', function () {
        $response = get(route('profile.passkeys.generate-options'));

        $response->assertRedirect(route('login', absolute: false));
    });

    it('requires authentication for passkey store', function () {
        $response = post(route('profile.passkeys.store'), [
            'passkey' => '{}',
            'options' => '{}',
        ]);

        $response->assertRedirect(route('login', absolute: false));
    });

    it('requires authentication for passkey deletion', function () {
        $passkey = Passkey::factory()->create();

        $response = delete(route('profile.passkeys.delete', $passkey->id));

        $response->assertRedirect(route('login', absolute: false));
    });

    it('handles special characters in password correctly', function () {
        $specialPassword = 'P@$$w0rd!#$%^&*()123';
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = actingAs($user)->put(route('user.settings.password.update'), [
            'current_password' => 'old-password',
            'password' => $specialPassword,
            'password_confirmation' => $specialPassword,
        ]);

        $response->assertRedirect();
        expect(Hash::check($specialPassword, $user->fresh()->password))->toBeTrue();
    });
});
