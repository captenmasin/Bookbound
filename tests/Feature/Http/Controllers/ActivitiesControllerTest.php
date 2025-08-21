<?php

use App\Models\User;
use App\Models\Activity;

use function Pest\Laravel\actingAs;

use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ActivitiesController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('displays user activities page', function () {
        Activity::factory()->for($this->user)->count(5)->create();

        $response = actingAs($this->user)->get(route('user.activities.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user/Activities')
            ->has('activities.data', 5)
            ->where('meta.title', 'Activities | Bookbound')
            ->where('meta.description', 'A list of your recent activities.')
        );
    });

    it('only shows authenticated user activities', function () {
        $otherUser = User::factory()->create();

        Activity::factory()->for($this->user)->count(3)->create();
        Activity::factory()->for($otherUser)->count(2)->create();

        $response = actingAs($this->user)->get(route('user.activities.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user/Activities')
            ->has('activities.data', 3)
        );
    });

    it('orders activities by created_at descending', function () {
        Activity::factory()->for($this->user)->create([
            'created_at' => now()->subDays(2),
            'type' => 'book.added',
        ]);

        Activity::factory()->for($this->user)->create([
            'created_at' => now()->subDay(),
            'type' => 'book.status.updated',
        ]);

        $response = actingAs($this->user)->get(route('user.activities.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user/Activities')
            ->where('activities.data.0.type', 'book.status.updated')
            ->where('activities.data.1.type', 'book.added')
        );
    });

    it('paginates activities with 10 per page', function () {
        Activity::factory()->for($this->user)->count(15)->create();

        $response = actingAs($this->user)->get(route('user.activities.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user/Activities')
            ->has('activities.data', 10)
            ->has('activities.links')
        );
    });

    it('preserves query string in pagination', function () {
        Activity::factory()->for($this->user)->count(15)->create();

        $response = actingAs($this->user)->get(route('user.activities.index', ['test' => 'param']));

        $response->assertSuccessful();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user/Activities')
            ->has('activities.data', 10)
        );
    });

    it('requires authentication to access activities', function () {
        $response = $this->get(route('user.activities.index'));

        $response->assertRedirect(route('login'));
    });

    it('transforms activities using ActivityResource', function () {
        $activity = Activity::factory()->for($this->user)->create([
            'type' => 'book.added',
        ]);

        $response = actingAs($this->user)->get(route('user.activities.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user/Activities')
            ->where('activities.data.0.id', $activity->id)
            ->where('activities.data.0.type', $activity->type)
            ->has('activities.data.0.description')
            ->has('activities.data.0.created_at')
        );
    });
});
