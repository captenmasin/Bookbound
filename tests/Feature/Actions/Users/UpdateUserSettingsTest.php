<?php

use App\Models\User;
use App\Actions\Users\UpdateUserSettings;

test('handle method updates multiple user settings', function () {
    $user = User::factory()->create();
    $action = new UpdateUserSettings;

    $settings = [
        'theme' => 'dark',
        'notifications' => true,
        'language' => 'en',
    ];

    $action->handle($user, $settings);

    foreach ($settings as $key => $value) {
        expect($user->settings()->get($key))->toBe($value);
    }
});

test('handle method works with different setting types', function () {
    $user = User::factory()->create();
    $action = new UpdateUserSettings;

    $settings = [
        'stringValue' => 'test',
        'booleanValue' => false,
        'integerValue' => 42,
        'arrayValue' => ['key' => 'value'],
    ];

    $action->handle($user, $settings);

    foreach ($settings as $key => $value) {
        expect($user->settings()->get($key))->toBe($value);
    }
});

test('handle method works with empty settings array', function () {
    $user = User::factory()->create();
    $action = new UpdateUserSettings;

    $settings = [];

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('No settings provided.');

    $action->handle($user, $settings);
});

test('asController returns success response with valid data', function () {
    $user = User::factory()->create();
    $action = new UpdateUserSettings;

    $settings = [
        'theme' => 'dark',
        'notifications' => false,
    ];

    $request = request();
    $request->merge(['settings' => $settings]);
    $request->setUserResolver(fn () => $user);

    $response = $action->asController($request);

    expect($response->getStatusCode())->toBe(200);

    $data = $response->getData(true);
    expect($data)->toHaveKey('success', true);
    expect($data)->toHaveKey('message', 'User settings updated successfully.');

    foreach ($settings as $key => $value) {
        expect($user->settings()->get($key))->toBe($value);
    }
});

test('asController validates required settings field', function () {
    $user = User::factory()->create();
    $action = new UpdateUserSettings;

    $request = request();
    $request->merge([]);
    $request->setUserResolver(fn () => $user);

    $response = $action->asController($request);

    expect($response->getStatusCode())->toBe(422);

    $data = $response->getData(true);
    expect($data)->toHaveKey('success', false);
    expect($data)->toHaveKey('errors');
    expect($data['errors'])->toHaveKey('settings');
});

test('asController validates settings must be array', function () {
    $user = User::factory()->create();
    $action = new UpdateUserSettings;

    $request = request();
    $request->merge(['settings' => 'not-an-array']);
    $request->setUserResolver(fn () => $user);

    $response = $action->asController($request);

    expect($response->getStatusCode())->toBe(422);

    $data = $response->getData(true);
    expect($data)->toHaveKey('success', false);
    expect($data)->toHaveKey('errors');
    expect($data['errors'])->toHaveKey('settings');
});

test('asController validates empty settings array as required field violation', function () {
    $user = User::factory()->create();
    $action = new UpdateUserSettings;

    $request = request();
    $request->merge(['settings' => []]);
    $request->setUserResolver(fn () => $user);

    $response = $action->asController($request);

    expect($response->getStatusCode())->toBe(422);

    $data = $response->getData(true);
    expect($data)->toHaveKey('success', false);
    expect($data)->toHaveKey('errors');
    expect($data['errors'])->toHaveKey('settings');
});

test('asController handles complex nested settings', function () {
    $user = User::factory()->create();
    $action = new UpdateUserSettings;

    $settings = [
        'preferences' => [
            'theme' => 'dark',
            'layout' => 'grid',
            'notifications' => [
                'email' => true,
                'push' => false,
            ],
        ],
        'bookmarks' => [1, 2, 3],
    ];

    $request = request();
    $request->merge(['settings' => $settings]);
    $request->setUserResolver(fn () => $user);

    $response = $action->asController($request);

    expect($response->getStatusCode())->toBe(200);

    foreach ($settings as $key => $value) {
        expect($user->settings()->get($key))->toBe($value);
    }
});
