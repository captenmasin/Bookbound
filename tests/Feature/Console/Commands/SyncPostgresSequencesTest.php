<?php

use App\Models\Book;
use App\Models\User;
use App\Enums\UserBookStatus;
use Illuminate\Support\Facades\DB;
use App\Actions\Books\AddBookToUser;

it('repairs library inserts when earlier tables are empty', function () {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('PostgreSQL is required to test sequences.');
    }

    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books->first(), [
        'id' => 10,
        'status' => UserBookStatus::PlanToRead->value,
    ]);
    DB::statement("SELECT setval(pg_get_serial_sequence('book_user', 'id'), 10, false)");

    $this->artisan('db:sync-sequences')->assertExitCode(0);

    AddBookToUser::run($books->last(), $user);

    $this->assertDatabaseHas('book_user', [
        'id' => 11,
        'book_id' => $books->last()->id,
        'user_id' => $user->id,
    ]);
    $this->assertDatabaseHas('activities', [
        'id' => 1,
        'user_id' => $user->id,
        'subject_id' => $books->last()->id,
    ]);
});

it('repairs a library sequence referenced by its default without column ownership', function () {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('PostgreSQL is required to test sequences.');
    }

    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();
    $user->books()->attach($books->first(), [
        'id' => 12,
        'status' => UserBookStatus::PlanToRead->value,
    ]);
    DB::statement('ALTER SEQUENCE book_user_id_seq OWNED BY NONE');
    DB::statement("SELECT setval('book_user_id_seq', 12, false)");

    $this->artisan('db:sync-sequences', ['--table' => 'book_user'])
        ->expectsOutput('Synced book_user.id → 12')
        ->assertExitCode(0);

    AddBookToUser::run($books->last(), $user);

    $this->assertDatabaseHas('book_user', [
        'id' => 13,
        'book_id' => $books->last()->id,
        'user_id' => $user->id,
    ]);
});

it('fails when the requested table has no sequence', function (string $table) {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('PostgreSQL is required to test sequences.');
    }

    $this->artisan('db:sync-sequences', ['--table' => $table])
        ->expectsOutput("No sequences found for table \"{$table}\".")
        ->assertExitCode(1);
})->with([
    'nonexistent table' => 'missing_table',
    'table without an incrementing key' => 'password_reset_tokens',
]);
