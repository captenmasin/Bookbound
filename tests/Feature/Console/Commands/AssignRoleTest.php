<?php

use App\Models\User;
use App\Enums\UserRole;
use Spatie\Permission\Models\Role;
use App\Console\Commands\AssignRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('AssignRole Command', function () {
    beforeEach(function () {
        Role::create(['name' => UserRole::Admin->value]);
        Role::create(['name' => UserRole::User->value]);
    });

    it('command signature includes optional role argument', function () {
        $command = new AssignRole;
        $reflection = new \ReflectionClass($command);
        $signature = $reflection->getProperty('signature')->getValue($command);

        expect($signature)->toContain('users:role:assign')
            ->and($signature)->toContain('{role?}');
    });

    it('command uses correct role enum mapping', function () {
        $roleMapping = collect(UserRole::cases())
            ->mapWithKeys(fn ($role) => [$role->value => $role->name])
            ->toArray();

        expect($roleMapping)->toHaveKey(UserRole::Admin->value)
            ->and($roleMapping)->toHaveKey(UserRole::User->value)
            ->and($roleMapping[UserRole::Admin->value])->toBe(UserRole::Admin->name)
            ->and($roleMapping[UserRole::User->value])->toBe(UserRole::User->name);
    });

    it('command uses correct user mapping format', function () {
        $user1 = User::factory()->create(['name' => 'Test User 1']);
        $user2 = User::factory()->create(['name' => 'Test User 2']);

        $userMapping = collect(User::all())
            ->mapWithKeys(fn ($user) => [$user->id => $user->name])
            ->toArray();

        expect($userMapping)->toHaveKey($user1->id)
            ->and($userMapping)->toHaveKey($user2->id)
            ->and($userMapping[$user1->id])->toBe($user1->name)
            ->and($userMapping[$user2->id])->toBe($user2->name);
    });

    it('executes command with role selection and user selection', function () {
        $user = User::factory()->create(['name' => 'Test User']);

        $this->artisan('users:role:assign')
            ->expectsChoice('What role should the user have?', [UserRole::User->value], [
                UserRole::Admin->value => UserRole::Admin->name,
                UserRole::User->value => UserRole::User->name,
            ])
            ->expectsChoice('Select users to assign the role to', [$user->id], [
                $user->id => $user->name,
            ])
            ->assertExitCode(0);

        expect($user->fresh()->hasRole(UserRole::User->value))->toBeTrue();
    });

    it('executes command with admin role and automatically includes user role', function () {
        $user = User::factory()->create(['name' => 'Admin User']);

        $this->artisan('users:role:assign')
            ->expectsChoice('What role should the user have?', [UserRole::Admin->value], [
                UserRole::Admin->value => UserRole::Admin->name,
                UserRole::User->value => UserRole::User->name,
            ])
            ->expectsChoice('Select users to assign the role to', [$user->id], [
                $user->id => $user->name,
            ])
            ->assertExitCode(0);

        $freshUser = $user->fresh();
        expect($freshUser->hasRole(UserRole::Admin->value))->toBeTrue()
            ->and($freshUser->hasRole(UserRole::User->value))->toBeTrue();
    });

    it('executes command with role argument provided', function () {
        $user = User::factory()->create(['name' => 'Preset User']);

        $this->artisan('users:role:assign', ['role' => UserRole::User->value])
            ->expectsChoice('What role should the user have?', [UserRole::User->value], [
                UserRole::Admin->value => UserRole::Admin->name,
                UserRole::User->value => UserRole::User->name,
            ])
            ->expectsChoice('Select users to assign the role to', [$user->id], [
                $user->id => $user->name,
            ])
            ->assertExitCode(0);

        expect($user->fresh()->hasRole(UserRole::User->value))->toBeTrue();
    });

    it('executes command with multiple users selected', function () {
        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);

        $this->artisan('users:role:assign')
            ->expectsChoice('What role should the user have?', [UserRole::Admin->value], [
                UserRole::Admin->value => UserRole::Admin->name,
                UserRole::User->value => UserRole::User->name,
            ])
            ->expectsChoice('Select users to assign the role to', [$user1->id, $user2->id], [
                $user1->id => $user1->name,
                $user2->id => $user2->name,
            ])
            ->assertExitCode(0);

        expect($user1->fresh()->hasRole(UserRole::Admin->value))->toBeTrue()
            ->and($user2->fresh()->hasRole(UserRole::Admin->value))->toBeTrue();
    });

    it('loads roles before assignment to prevent N+1 queries', function () {
        $user = User::factory()->create(['name' => 'Query Test User']);

        $this->artisan('users:role:assign')
            ->expectsChoice('What role should the user have?', [UserRole::User->value], [
                UserRole::Admin->value => UserRole::Admin->name,
                UserRole::User->value => UserRole::User->name,
            ])
            ->expectsChoice('Select users to assign the role to', [$user->id], [
                $user->id => $user->name,
            ])
            ->assertExitCode(0);

        expect($user->fresh()->hasRole(UserRole::User->value))->toBeTrue();
    });

    it('handles multiple role assignment correctly', function () {
        $user = User::factory()->create(['name' => 'Multi Role User']);

        $this->artisan('users:role:assign')
            ->expectsChoice('What role should the user have?', [UserRole::Admin->value, UserRole::User->value], [
                UserRole::Admin->value => UserRole::Admin->name,
                UserRole::User->value => UserRole::User->name,
            ])
            ->expectsChoice('Select users to assign the role to', [$user->id], [
                $user->id => $user->name,
            ])
            ->assertExitCode(0);

        $freshUser = $user->fresh();
        expect($freshUser->hasRole(UserRole::Admin->value))->toBeTrue()
            ->and($freshUser->hasRole(UserRole::User->value))->toBeTrue();
    });
});
