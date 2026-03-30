<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use App\Models\Activity;
use App\Enums\UserBookStatus;

use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\Http;
use App\Ai\Agents\BookRecommendationAgent;

describe('DashboardController', function () {
    it('redirects guests to the login page', function () {
        $response = get('/dashboard');
        $response->assertRedirect('/login');
    });

    it('allows authenticated users to view their dashboard', function () {
        $user = User::factory()->create();
        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
    });

    it('includes live weather on the dashboard when geolocation and forecast lookups succeed', function () {
        Http::fake([
            'https://ipapi.co/*' => Http::response([
                'latitude' => 51.5072,
                'longitude' => -0.1276,
            ]),
            'https://api.open-meteo.com/v1/forecast*' => Http::response([
                'current' => [
                    'weather_code' => 0,
                    'is_day' => 1,
                ],
            ]),
        ]);

        $user = User::factory()->create();
        actingAs($user);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('weather.condition', 'sunny')
            ->where('weather.icon', 'Sun')
            ->where('weather.label', 'Sunny')
            ->where('weather.isFallback', false)
        );
    });

    it('maps rainy and stormy weather codes to normalized dashboard weather states', function () {
        Http::fake([
            'https://ipapi.co/*' => Http::response([
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ]),
            'https://api.open-meteo.com/v1/forecast*' => Http::sequence()
                ->push([
                    'current' => [
                        'weather_code' => 61,
                        'is_day' => 1,
                    ],
                ])
                ->push([
                    'current' => [
                        'weather_code' => 95,
                        'is_day' => 1,
                    ],
                ]),
        ]);

        $firstUser = User::factory()->create();
        actingAs($firstUser);

        $rainyResponse = $this
            ->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
            ->get('/dashboard');

        $rainyResponse->assertInertia(fn ($page) => $page
            ->where('weather.condition', 'rainy')
            ->where('weather.icon', 'CloudRain')
            ->where('weather.label', 'Rainy')
        );

        $stormyUser = User::factory()->create();
        actingAs($stormyUser);

        $stormyResponse = $this
            ->withServerVariables(['REMOTE_ADDR' => '9.9.9.9'])
            ->get('/dashboard');

        $stormyResponse->assertInertia(fn ($page) => $page
            ->where('weather.condition', 'stormy')
            ->where('weather.icon', 'CloudLightning')
            ->where('weather.label', 'Stormy')
        );
    });

    it('returns time-of-day fallback weather when geolocation fails', function () {
        Http::fake([
            'https://ipapi.co/*' => Http::response([], 500),
        ]);

        $user = User::factory()->create();
        actingAs($user);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '8.8.4.4'])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('weather.condition', 'time_of_day')
            ->where('weather.icon', 'Sun')
            ->where('weather.label', 'Time of day')
            ->where('weather.isFallback', true)
        );
    });

    it('returns time-of-day fallback weather when the forecast lookup fails', function () {
        Http::fake([
            'https://ipapi.co/*' => Http::response([
                'latitude' => 34.0522,
                'longitude' => -118.2437,
            ]),
            'https://api.open-meteo.com/v1/forecast*' => Http::response([], 500),
        ]);

        $user = User::factory()->create();
        actingAs($user);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '208.67.222.222'])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('weather.condition', 'time_of_day')
            ->where('weather.icon', 'Sun')
            ->where('weather.label', 'Time of day')
            ->where('weather.isFallback', true)
        );
    });

    it('displays user information on the dashboard', function () {
        $user = User::factory()->create();
        actingAs($user);

        $response = get('/dashboard');
        $response->assertSee($user->name);
    });

    it('displays dashboard with books in different statuses', function () {
        $user = User::factory()->create();

        // Create books with different statuses
        $readingBook = Book::factory()->create();
        $readBook = Book::factory()->create();
        $planToReadBook = Book::factory()->create();

        $user->books()->attach($readingBook->id, ['status' => UserBookStatus::Reading->value]);
        $user->books()->attach($readBook->id, ['status' => UserBookStatus::Read->value]);
        $user->books()->attach($planToReadBook->id, ['status' => UserBookStatus::PlanToRead->value]);

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('statValues', fn ($statValues) => $statValues
                ->where('booksInLibrary', 3)
                ->where('completedBooks', 1)
                ->where('readingBooks', 1)
                ->where('planToRead', 1)
            )
            ->has('recentlyAdded', 3)
            ->has('insights', fn ($insights) => $insights
                ->where('totalPagesOwned', $readingBook->page_count + $readBook->page_count + $planToReadBook->page_count)
                ->where('completedPages', $readBook->page_count)
                ->where('recentAddsLast30Days', 3)
                ->where('completionRate', 33)
            )
        );
    });

    it('calculates statistics correctly with multiple books', function () {
        $user = User::factory()->create();

        $books = Book::factory()->count(10)->create();

        // Attach books with various statuses
        $user->books()->attach($books[0]->id, ['status' => UserBookStatus::Reading->value]);
        $user->books()->attach($books[1]->id, ['status' => UserBookStatus::Reading->value]);
        $user->books()->attach($books[2]->id, ['status' => UserBookStatus::Read->value]);
        $user->books()->attach($books[3]->id, ['status' => UserBookStatus::Read->value]);
        $user->books()->attach($books[4]->id, ['status' => UserBookStatus::Read->value]);
        $user->books()->attach($books[5]->id, ['status' => UserBookStatus::PlanToRead->value]);
        $user->books()->attach($books[6]->id, ['status' => UserBookStatus::PlanToRead->value]);
        $user->books()->attach($books[7]->id, ['status' => UserBookStatus::PlanToRead->value]);
        $user->books()->attach($books[8]->id, ['status' => UserBookStatus::PlanToRead->value]);
        $user->books()->attach($books[9]->id, ['status' => UserBookStatus::OnHold->value]);

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('statValues', fn ($statValues) => $statValues
                ->where('booksInLibrary', 10)
                ->where('completedBooks', 3)
                ->where('readingBooks', 2)
                ->where('planToRead', 4)
            )
        );
    });

    it('displays currently reading books section limited to 4 books', function () {
        $user = User::factory()->create();

        $readingBooks = Book::factory()->count(6)->create();

        foreach ($readingBooks as $book) {
            $user->books()->attach($book->id, ['status' => UserBookStatus::Reading->value]);
        }

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('currentlyReading', 4)
        );
    });

    it('displays top authors based on read books', function () {
        $user = User::factory()->create();

        // Create authors and books
        $author1 = Author::factory()->create();
        $author2 = Author::factory()->create();
        $author3 = Author::factory()->create();

        // Create books by different authors
        $books1 = Book::factory()->count(3)->create();
        $books2 = Book::factory()->count(2)->create();
        $books3 = Book::factory()->count(1)->create();

        // Attach authors to books
        foreach ($books1 as $book) {
            $book->authors()->attach($author1->id);
            $user->books()->attach($book->id, ['status' => UserBookStatus::Read->value]);
        }

        foreach ($books2 as $book) {
            $book->authors()->attach($author2->id);
            $user->books()->attach($book->id, ['status' => UserBookStatus::Read->value]);
        }

        foreach ($books3 as $book) {
            $book->authors()->attach($author3->id);
            $user->books()->attach($book->id, ['status' => UserBookStatus::Read->value]);
        }

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('authors')
            ->where('authors.0.name', $author1->name)
        );
    });

    it('displays top tags from user books', function () {
        $user = User::factory()->create();

        $tags = Tag::factory()->count(5)->create();
        $books = Book::factory()->count(3)->create();

        // Attach tags to books and books to user
        foreach ($books as $book) {
            $book->tags()->attach($tags->random(3)->pluck('id'));
            $user->books()->attach($book->id, ['status' => UserBookStatus::Read->value]);
        }

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('tags')
        );
    });

    it('displays recent user activities', function () {
        $user = User::factory()->create();

        $activities = Activity::factory()->count(10)->for($user)->create();

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('activities', 5)
        );
    });

    it('handles user with no books gracefully', function () {
        $user = User::factory()->create();
        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('statValues', fn ($statValues) => $statValues
                ->where('booksInLibrary', 0)
                ->where('completedBooks', 0)
                ->where('readingBooks', 0)
                ->where('planToRead', 0)
            )
            ->has('currentlyReading', 0)
            ->has('recentlyAdded', 0)
            ->has('insights', fn ($insights) => $insights
                ->where('totalPagesOwned', 0)
                ->where('completedPages', 0)
                ->where('recentAddsLast30Days', 0)
                ->where('completionRate', 0)
            )
            ->has('activities')
            ->has('tags', 0)
            ->has('authors', 0)
        );
    });

    it('orders books by most recently added to library', function () {
        $user = User::factory()->create();

        $oldBook = Book::factory()->create();
        $newBook = Book::factory()->create();

        // Add old book first
        $user->books()->attach($oldBook->id, [
            'status' => UserBookStatus::Reading->value,
            'created_at' => now()->subDays(5),
        ]);

        // Add new book later
        $user->books()->attach($newBook->id, [
            'status' => UserBookStatus::Reading->value,
            'created_at' => now(),
        ]);

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('currentlyReading', 2)
            ->where('currentlyReading.0.id', $newBook->id)
        );
    });

    it('limits recently added books to three and orders them by most recent attachment', function () {
        $user = User::factory()->create();

        $books = Book::factory()->count(4)->create();

        foreach ($books as $index => $book) {
            $user->books()->attach($book->id, [
                'status' => UserBookStatus::PlanToRead->value,
                'created_at' => now()->subDays(4 - $index),
            ]);
        }

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('recentlyAdded', 3)
            ->where('recentlyAdded.0.id', $books[3]->id)
            ->where('recentlyAdded.1.id', $books[2]->id)
            ->where('recentlyAdded.2.id', $books[1]->id)
        );
    });

    it('calculates dashboard insights from the user library', function () {
        $user = User::factory()->create();

        $readingBook = Book::factory()->create(['page_count' => 120]);
        $completedBookOne = Book::factory()->create(['page_count' => 310]);
        $completedBookTwo = Book::factory()->create(['page_count' => 190]);
        $queuedBook = Book::factory()->create(['page_count' => 80]);

        $user->books()->attach($readingBook->id, [
            'status' => UserBookStatus::Reading->value,
            'created_at' => now()->subDays(7),
        ]);
        $user->books()->attach($completedBookOne->id, [
            'status' => UserBookStatus::Read->value,
            'created_at' => now()->subDays(20),
        ]);
        $user->books()->attach($completedBookTwo->id, [
            'status' => UserBookStatus::Read->value,
            'created_at' => now()->subDays(45),
        ]);
        $user->books()->attach($queuedBook->id, [
            'status' => UserBookStatus::PlanToRead->value,
            'created_at' => now()->subDays(5),
        ]);

        actingAs($user);

        $response = get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('insights', fn ($insights) => $insights
                ->where('totalPagesOwned', 700)
                ->where('completedPages', 500)
                ->where('recentAddsLast30Days', 3)
                ->where('completionRate', 50)
            )
        );
    });

    it('displays ai-powered recommendations based on the user library', function () {
        $user = User::factory()->create();
        $author = Author::factory()->create([
            'name' => 'Frank Herbert',
        ]);
        $tag = Tag::factory()->create([
            'name' => 'Space Opera',
        ]);

        $readBook = Book::factory()->create([
            'title' => 'Dune',
            'categories' => ['Science Fiction'],
            'language' => 'en',
        ]);
        $readingBook = Book::factory()->create([
            'title' => 'Hyperion',
            'categories' => ['Science Fiction'],
            'language' => 'en',
        ]);
        $recommendedBook = Book::factory()->create([
            'title' => 'Children of Time',
            'categories' => ['Science Fiction'],
            'language' => 'en',
        ]);

        $readBook->authors()->attach($author);
        $readBook->tags()->attach($tag);
        $readingBook->authors()->attach($author);
        $readingBook->tags()->attach($tag);
        $recommendedBook->tags()->attach($tag);

        $user->books()->attach($readBook->id, ['status' => UserBookStatus::Read->value]);
        $user->books()->attach($readingBook->id, ['status' => UserBookStatus::Reading->value]);

        BookRecommendationAgent::fake([
            ['recommendations' => [
                [
                    'title' => 'Children of Time',
                    'author' => 'Adrian Tchaikovsky',
                    'published_year' => 2015,
                    'reason' => 'Fits your recent run of big-idea science fiction.',
                ],
            ]],
        ]);

        actingAs($user);

        $response = get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('statValues')
        );
    });
});
