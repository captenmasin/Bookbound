<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use App\Models\Rating;
use App\Models\Review;
use App\Enums\UserRole;
use App\Models\Publisher;
use App\Enums\UserBookStatus;
use App\Mail\ContactFormSubmission;
use App\Actions\Books\AddBookToUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;

Artisan::command('user:admin {user}', function () {
    $user = User::findOrFail($this->argument('user'));

    $user->assignRole(UserRole::User);
    $user->assignRole(UserRole::Admin);

    $this->info("Granted admin role to {$user->name} (ID: {$user->id}).");
})->purpose('Grant the admin role to an existing user by ID');

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

Artisan::command('mail:test', function () {
    $user = User::find(1);
    Mail::to($user->email)->send(
        new ContactFormSubmission(
            name: $user->name,
            email: $user->email,
            message: 'Hello '.$user->name.' this is just a test',
            userId: auth()->id() ?? null,
        )->subject('test'));
});
