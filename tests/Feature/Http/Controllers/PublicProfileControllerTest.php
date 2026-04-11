<?php

use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use App\Models\Activity;
use App\Enums\ActivityType;
use App\Enums\UserBookStatus;

use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\GenerateSitemap;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PublicProfileController', function () {
    it('renders the public profile page for a public user', function () {
        $user = User::factory()->create([
            'name' => 'Mason',
            'username' => 'mason',
        ]);

        $book = Book::withoutEvents(fn () => Book::query()->create([
            'title' => 'Example Book',
            'description' => 'Example description',
            'identifier' => 'example-book',
            'path' => 'example-book',
            'codes' => '[]',
            'original_cover' => 'https://placehold.co/500x800',
        ]));

        Review::factory()->for($user)->for($book)->create([
            'title' => 'Great read',
            'content' => 'Very good.',
        ]);

        $secondBook = Book::withoutEvents(fn () => Book::query()->create([
            'title' => 'Read Book',
            'description' => 'Read description',
            'identifier' => 'read-book',
            'path' => 'read-book',
            'codes' => '[]',
            'original_cover' => 'https://placehold.co/500x800',
        ]));

        $user->books()->attach($book->id, ['status' => UserBookStatus::Reading->value]);
        $user->books()->attach($secondBook->id, ['status' => UserBookStatus::Read->value]);

        Activity::factory()->for($user)->create([
            'type' => ActivityType::BookAdded->value,
            'properties' => ['book_title' => 'Example Book'],
        ]);

        $response = get(route('profiles.show', $user));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('profiles/Show')
            ->where('user.name', 'Mason')
            ->where('user.username', 'mason')
            ->where('user.books_count', 2)
            ->where('user.books_read_count', 1)
            ->has('reviews.data', 1)
            ->has('activities.data', 1)
            ->where('activities.data.0.description', 'Mason added <strong>Example Book</strong> to their library as <em>unknown</em>.')
            ->where('is_owner', false)
            ->where('meta.title', 'Mason (@mason) | Bookbound')
        );
    });

    it('returns not found for an unknown username', function () {
        get('/@missing-user')->assertNotFound();
    });

    it('returns not found for guests when a profile is private', function () {
        $user = User::factory()->create(['username' => 'private-user']);
        $user->settings()->update('profile.is_private', true);

        get(route('profiles.show', $user))->assertNotFound();
    });

    it('returns not found for other authenticated users when a profile is private', function () {
        $user = User::factory()->create(['username' => 'private-user']);
        $user->settings()->update('profile.is_private', true);

        actingAs(User::factory()->create())
            ->get(route('profiles.show', $user))
            ->assertNotFound();
    });

    it('allows the owner to view their own private profile', function () {
        $user = User::factory()->create(['username' => 'private-owner']);
        $user->settings()->update('profile.is_private', true);

        $response = actingAs($user)->get(route('profiles.show', $user));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('profiles/Show')
            ->where('is_owner', true)
        );
    });

    it('does not expose private account fields in the public payload', function () {
        $user = User::factory()->create([
            'username' => 'public-user',
            'email' => 'private@example.com',
        ]);

        $response = get(route('profiles.show', $user));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('user.email')
            ->missing('user.permissions')
            ->missing('user.settings')
            ->missing('user.subscription')
            ->missing('user.book_identifiers')
        );
    });

    it('includes only public profiles in the sitemap', function () {
        $publicUser = User::factory()->create(['username' => 'public-user']);
        $privateUser = User::factory()->create(['username' => 'private-user']);
        $privateUser->settings()->update('profile.is_private', true);

        Artisan::call(GenerateSitemap::class);

        $sitemap = File::get(public_path('sitemap_profiles.xml'));

        expect($sitemap)
            ->toContain(route('profiles.show', $publicUser))
            ->not->toContain(route('profiles.show', $privateUser));
    });
});
