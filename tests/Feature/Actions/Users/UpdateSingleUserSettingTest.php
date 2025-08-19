<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

use App\Actions\Users\UpdateSingleUserSetting;

test('handle method updates user setting', function () {
    $user = User::factory()->create();
    $action = new UpdateSingleUserSetting;

    $settingName = 'theme';
    $value = 'dark';

    $action->handle($user, $settingName, $value);

    expect($user->settings()->get($settingName))->toBe($value);
});

test('handle method works with different setting types', function () {
    $user = User::factory()->create();
    $action = new UpdateSingleUserSetting;

    $testCases = [
        ['notifications', true],
        ['fontSize', 16],
        ['language', 'en'],
        ['preferences', ['key' => 'value']],
    ];

    foreach ($testCases as [$settingName, $value]) {
        $action->handle($user, $settingName, $value);
        expect($user->settings()->get($settingName))->toBe($value);
    }
});

test('asController returns success response with valid data', function () {
    $user = User::factory()->create();
    $action = new UpdateSingleUserSetting;

    $request = request();
    $request->merge([
        'setting' => 'theme',
        'value' => 'light',
    ]);
    $request->setUserResolver(fn () => $user);

    $response = $action->asController($request);

    expect($response->getStatusCode())->toBe(200);

    $data = $response->getData(true);
    expect($data)->toHaveKey('message', 'User settings updated successfully.');
    expect($data)->toHaveKey('setting', 'theme');
    expect($data)->toHaveKey('value', 'light');

    expect($user->settings()->get('theme'))->toBe('light');
});

test('asController validates required setting field', function () {
    $user = User::factory()->create();
    $action = new UpdateSingleUserSetting;

    $request = request();
    $request->merge([
        'value' => 'some-value',
    ]);
    $request->setUserResolver(fn () => $user);

    $response = $action->asController($request);

    expect($response->getStatusCode())->toBe(422);

    $data = $response->getData(true);
    expect($data)->toHaveKey('errors');
    expect($data['errors'])->toHaveKey('setting');
});

test('asController validates required value field', function () {
    $user = User::factory()->create();
    $action = new UpdateSingleUserSetting;

    $request = request();
    $request->merge([
        'setting' => 'theme',
    ]);
    $request->setUserResolver(fn () => $user);

    $response = $action->asController($request);

    expect($response->getStatusCode())->toBe(422);

    $data = $response->getData(true);
    expect($data)->toHaveKey('errors');
    expect($data['errors'])->toHaveKey('value');
});

test('asController validates setting must be string', function () {
    $user = User::factory()->create();
    $action = new UpdateSingleUserSetting;

    $request = request();
    $request->merge([
        'setting' => 123,
        'value' => 'some-value',
    ]);
    $request->setUserResolver(fn () => $user);

    $response = $action->asController($request);

    expect($response->getStatusCode())->toBe(422);

    $data = $response->getData(true);
    expect($data)->toHaveKey('errors');
    expect($data['errors'])->toHaveKey('setting');
});

test('asController handles various value types', function () {
    $user = User::factory()->create();
    $action = new UpdateSingleUserSetting;

    $testCases = [
        ['string_setting', 'string_value'],
        ['boolean_setting', true],
        ['integer_setting', 42],
        ['array_setting', ['key' => 'value']],
    ];

    foreach ($testCases as [$settingName, $value]) {
        $request = request();
        $request->merge([
            'setting' => $settingName,
            'value' => $value,
        ]);
        $request->setUserResolver(fn () => $user);

        $response = $action->asController($request);

        expect($response->getStatusCode())->toBe(200)
            ->and($user->settings()->get($settingName))->toBe($value);
    }
});

test('action can be used as route controller', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->patchJson('/api/user/settings/single', [
        'setting' => 'theme',
        'value' => 'dark',
    ]);

    $response->assertOk();
    $response->assertJson([
        'message' => 'User settings updated successfully.',
        'setting' => 'theme',
        'value' => 'dark',
    ]);

    expect($user->settings()->get('theme'))->toBe('dark');
});

test('action requires authentication when used as route controller', function () {
    $response = $this
        ->patchJson('/api/user/settings/single', [
            'setting' => 'theme',
            'value' => 'dark',
        ]);

    $response->assertUnauthorized();
});
