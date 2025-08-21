<?php

use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;

use Illuminate\Http\UploadedFile;

use function Pest\Laravel\actingAs;

use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Settings\\ProfileController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('renders the profile settings page for authenticated users', function () {
        $response = actingAs($this->user)->get(route('user.settings.profile.edit'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('settings/Profile')
            ->where('mustVerifyEmail', true)
            ->where('meta.title', 'Profile Settings | Bookbound')
            ->where('meta.description', 'Manage your profile information, including your name, email, and avatar.')
        );
    });

    it('redirects guest users to login page', function () {
        $response = get(route('user.settings.profile.edit'));

        $response->assertRedirect(route('login', absolute: false));
    });

    it('updates profile details with avatar and profile colour', function () {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = actingAs($this->user)->post(route('user.settings.profile.update'), [
            'name' => 'New Name',
            'username' => 'newusername',
            'email' => 'new@example.com',
            'avatar' => $file,
            'profile_colour' => '#123456',
        ]);

        $response->assertRedirect(route('user.settings.profile.edit', absolute: false));

        $this->user->refresh();

        expect($this->user->name)->toBe('New Name')
            ->and($this->user->email)->toBe('new@example.com')
            ->and($this->user->username)->toBe('newusername')
            ->and($this->user->email_verified_at)->toBeNull()
            ->and($this->user->hasMedia('avatar'))->toBeTrue()
            ->and($this->user->settings()->get('profile.colour'))->toBe('#123456');
    });

    it('deletes the user avatar and flashes success', function () {
        // Seed an avatar on the user
        $seed = UploadedFile::fake()->image('seed.jpg');
        actingAs($this->user)->post(route('user.settings.profile.update'), [
            'name' => $this->user->name,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'avatar' => $seed,
        ])->assertRedirect();

        expect($this->user->fresh()->hasMedia('avatar'))->toBeTrue();

        $response = actingAs($this->user)
            ->from(route('user.settings.profile.edit'))
            ->delete(route('user.settings.profile.avatar.destroy'));

        $response->assertRedirect(route('user.settings.profile.edit', absolute: false));
        $response->assertSessionHas('success', 'Your avatar has been deleted.');

        expect($this->user->fresh()->hasMedia('avatar'))->toBeFalse();
    });

    it('renders the danger zone page for authenticated users', function () {
        $response = actingAs($this->user)->get(route('user.settings.profile.danger'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('settings/Danger')
            ->where('meta.title', 'Danger Zone | Bookbound')
            ->where('meta.description', 'Delete your account.')
        );
    });

    it('requires authentication for avatar deletion and profile update', function () {
        // Avatar deletion
        $deleteResponse = delete(route('user.settings.profile.avatar.destroy'));
        $deleteResponse->assertRedirect(route('login', absolute: false));

        // Profile update
        $postResponse = post(route('user.settings.profile.update'), []);
        $postResponse->assertRedirect(route('login', absolute: false));
    });
});
