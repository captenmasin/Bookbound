<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\User;
use App\Models\Cover;
use App\Models\Author;
use App\Models\Rating;
use App\Models\Review;
use App\Models\Activity;
use App\Models\Publisher;
use Illuminate\Support\Str;
use App\Enums\UserBookStatus;
use Illuminate\Support\Facades\DB;
use App\Actions\Books\AddBookToUser;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ConfigureRelatedBooks;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

Schedule::command('horizon:snapshot')->everyMinute();
// Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command(ConfigureRelatedBooks::class)->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('make:admin', function () {
    $name = \Laravel\Prompts\text('Name:');
    $username = \Laravel\Prompts\text('Username:');
    $email = \Laravel\Prompts\text('Email:');
    $password = \Laravel\Prompts\password('Password:');

    $user = User::create([
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'password' => bcrypt($password),
    ]);

    $user->verifyEmail();

    $user->assignRole(\App\Enums\UserRole::User);
    $user->assignRole(\App\Enums\UserRole::Admin);
});

Artisan::command('reset', function () {
    Book::all()->each(fn ($book) => $book->delete());
    Cover::all()->each(fn ($cover) => $cover->delete());
    Tag::all()->each(fn ($tag) => $tag->delete());
    Activity::all()->each(fn ($activity) => $activity->delete());
    Publisher::all()->each(fn ($publisher) => $publisher->delete());
    \App\Models\PreviousSearch::all()->each(fn ($search) => $search->delete());
    Media::where('model_type', Cover::class)->get()->each(fn ($book) => $book->delete());
    DB::table('book_user')->truncate();
    DB::table('author_book')->truncate();
    DB::table('authors')->truncate();
    DB::table('book_tag')->truncate();

    User::all()
        ->each(function ($user) {
            $user->forceFill([
                'stripe_id' => null,
            ]);
            $user->save();
        });

    //    $admins = User::role('admin')->pluck('id');
    //
    //    User::whereNotIn('id', $admins)->get()->each(function ($user) {
    //        $user->books()->detach();
    //        $user->ratings()->delete();
    //        $user->notes()->delete();
    //        $user->reviews()->delete();
    //        $user->delete();
    //    });
});

Artisan::command('slug', function () {
    Tag::all()->each(function ($tag) {
        if (Tag::where('slug', $tag->slug)->exists()) {
            $slug = Str::slug($tag->name).'-'.Str::random(5);
        } else {
            $slug = Str::slug($tag->name);
        }

        $tag->update(['slug' => $slug]);
    });

    Author::all()->each(function ($author) {
        if (Author::where('slug', $author->slug)->exists()) {
            $slug = Str::slug($author->name).'-'.Str::random(5);
        } else {
            $slug = Str::slug($author->name);
        }

        $author->update(['slug' => $slug]);
    });
});

Artisan::command('flood', function () {
    User::factory(300)->create();
    $users = User::all();

    $books = Book::factory(1000)->create();
    $tags = Tag::factory(2000)->create();
    $publishers = Publisher::factory(300)->create();

    $authors = Author::factory(200)->create();

    $books->each(function ($book) use ($tags, $authors, $publishers) {
        $book->tags()->sync($tags->random(rand(1, 10))->pluck('id'));
        $book->authors()->sync($authors->random(rand(1, 3))->pluck('id'));

        $book->publisher()->associate($publishers->random());
        $book->save();
    });

    $users->each(function ($user) use ($books) {
        $count = min(rand(1, 30), $books->count());
        foreach ($books->random($count) as $book) {
            $status = collect(UserBookStatus::cases())->random();
            AddBookToUser::run($book, $user, $status);
        }
    });

    $users->each(function ($user) {
        $user->load('books');
        $user->books->each(function ($book) use ($user) {
            foreach (range(1, rand(10, 25)) as $i) {
                if ($i > 20 && rand(0, 1) === 0) {
                    continue; // Skip some iterations to reduce load
                }

                $book->notes()->create([
                    'user_id' => $user->id,
                    'content' => fake()->paragraph(),
                    'book_status' => collect(UserBookStatus::cases())->random()->value,
                ]);
            }

            if (rand(0, 1) === 1) {
                Rating::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'book_id' => $book->id,
                    ],
                    ['value' => rand(1, 5)]
                );
            }

            if (rand(0, 1) === 1) {
                Review::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'book_id' => $book->id,
                    ],
                    [
                        'title' => fake()->sentence(),
                        'content' => implode("\n\n", fake()->paragraphs(3)),
                    ]
                );
            }
        });
    });
});
