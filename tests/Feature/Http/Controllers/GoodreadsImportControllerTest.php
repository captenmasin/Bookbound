<?php

use App\Models\User;
use App\Models\GoodreadsImport;
use Illuminate\Http\UploadedFile;
use App\Jobs\StartGoodreadsImport;
use App\Enums\GoodreadsImportStatus;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Storage;

test('it shows the goodreads import page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('user.books.imports.create'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('books/ImportGoodreads')
        ->has('recentImports')
        ->where('activeImport', null)
    );
});

test('it stores an import and queues the coordinator job', function () {
    Storage::fake();
    Queue::fake();

    $user = User::factory()->create();
    $file = UploadedFile::fake()->createWithContent('goodreads_library_export.csv', "Title,Author,Additional Authors,ISBN,ISBN13,My Rating,Date Read,Date Added,Bookshelves,Exclusive Shelf,My Review,Private Notes\nBook,Author,,,0,,2025/12/29,,to-read,,\n");

    $response = $this->actingAs($user)->post(route('user.books.imports.store'), [
        'file' => $file,
    ]);

    $import = GoodreadsImport::first();

    $response->assertRedirect(route('user.books.imports.show', $import));

    expect($import)->not->toBeNull()
        ->and($import->status)->toBe(GoodreadsImportStatus::Pending)
        ->and(Storage::exists($import->file_path))->toBeTrue();

    Queue::assertPushed(StartGoodreadsImport::class, fn (StartGoodreadsImport $job) => $job->goodreadsImportId === $import->id);
});

test('it redirects to the active import instead of creating a duplicate active import', function () {
    Storage::fake();
    Queue::fake();

    $user = User::factory()->create();
    $activeImport = GoodreadsImport::factory()->for($user)->create([
        'status' => GoodreadsImportStatus::Processing,
    ]);

    $file = UploadedFile::fake()->createWithContent('goodreads_library_export.csv', "Title,Author,Additional Authors,ISBN,ISBN13,My Rating,Date Read,Date Added,Bookshelves,Exclusive Shelf,My Review,Private Notes\n");

    $response = $this->actingAs($user)->post(route('user.books.imports.store'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('user.books.imports.show', $activeImport));
    expect(GoodreadsImport::count())->toBe(1);
    Queue::assertNothingPushed();
});

test('it only shows import detail pages for the owning user', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $import = GoodreadsImport::factory()->for($owner)->create();

    $this->actingAs($otherUser)
        ->get(route('user.books.imports.show', $import))
        ->assertNotFound();
});
