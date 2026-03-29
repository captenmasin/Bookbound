<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\User;
use App\Models\Cover;
use App\Models\Author;
use App\Models\Rating;
use App\Models\Review;
use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\Publisher;
use Illuminate\Support\Str;
use App\Enums\UserBookStatus;
use App\Models\PreviousSearch;
use Illuminate\Support\Facades\DB;
use App\Actions\Books\AddBookToUser;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Actions\Books\ImportBookFromData;
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

    $user->assignRole(UserRole::User);
    $user->assignRole(UserRole::Admin);
});

Artisan::command('reset', function () {
    Book::all()->each(fn ($book) => $book->delete());
    Cover::all()->each(fn ($cover) => $cover->delete());
    Tag::all()->each(fn ($tag) => $tag->delete());
    Activity::all()->each(fn ($activity) => $activity->delete());
    Publisher::all()->each(fn ($publisher) => $publisher->delete());
    PreviousSearch::all()->each(fn ($search) => $search->delete());
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

Artisan::command('post:deploy', function () {
    $userBooks = DB::table('book_user')->get();
    foreach ($userBooks as $userBook) {
        if ($userBook->status === 'Completed') {
            DB::table('book_user')
                ->where('id', $userBook->id)
                ->update(['status' => UserBookStatus::Read]);
        }
    }
});

Artisan::command('pls', function () {
    $identifiers = [
        9781529077155,
        9781409169024,
        9781789461152,
        9780755501618,
        9780008297152,
        9781784752224,
        9781803364032,
        9781399607735,
        9781444780840,
        9780141442464,
        9781473681286,
        9781444764901,
        9780099528128,
        9781399810050,
        9781398845206,
        9781803368801,
        9780099532927,
        9780575084353,
        9780715653739,
        9780719810817,
        9781399705455,
        9781447264507,
        9780712353496,
        9781454953371,
        9780349145150,
        9781789293609,
        9780008567132,
        9781447220039,
        9781840228311,
        9780099527497,
        9780008667368,
        9781526646651,
        9781409182092,
        9781398507692,
        9780008277802,
        9780241996607,
        9781529355413,
        9781781255674,
        9781444720723,
        9781849015882,
        9781803366555,
        9781405965347,
        9781804185902,
        9780141182605,
        9781804944271,
        9781529077292,
        9781806770021,
        9780099532934,
        9781529920598,
        9780575075542,
        9781784752231,
        9780008567125,
        9780552166775,
        9780008279554,
        9781473222687,
        9781444793239,
        9781444720716,
        9781835412428,
        9780345516862,
        9780684194264,
        9781451687989,
        9781607109327,
        9780345418913,
        9781542099653,
        9781401246587,
        9780385532198,
        9781537822075,
        9780061760877,
        9780307781888,
        9781542099370,
        9781542099059,
        9781302482602,
        9781542097697,
        9781542049146,
        9781779501837,
        9781471419973,
        9781789091977,
        9780547951973,
        9780393355949,
        9781690564126,
        9780007322503,
        9780593733417,
        9780316273404,
        9780547952017,
        9780547952048,
        9780345504975,
        9781399731294,
        9781774267479,
        9780593203392,
        9781400079070,
        9780316041782,
        9781302938307,
        9780008660659,
        9781101530641,
        9780061974168,
        9781542097536,
        9780141969978,
        9781974701261,
        9780571191475,
        9781632360403,
        9781399725118,
        9781915275011,
        9780006755135,
        9780316041829,
        9780307351487,
        9781405292306,
    ];

    $user = User::first();

    foreach ($identifiers as $identifier) {
        if ($user->books()->where('identifier', $identifier)->exists()) {
            continue;
        }

        $book = ImportBookFromData::run($identifier);
        $user->books()->attach($book);
    }
});
