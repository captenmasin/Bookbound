<?php

use App\Models\User;
use App\Models\GoodreadsImport;
use App\Jobs\StartGoodreadsImport;
use Illuminate\Support\Facades\Bus;
use App\Enums\GoodreadsImportStatus;
use Illuminate\Support\Facades\Storage;

test('it creates a batch of row import jobs from a valid goodreads csv', function () {
    Storage::fake();
    Bus::fake();

    $user = User::factory()->create();
    $import = GoodreadsImport::factory()->for($user)->create([
        'file_path' => 'goodreads-imports/test.csv',
    ]);

    Storage::put($import->file_path, implode("\n", [
        'Title,Author,Additional Authors,ISBN,ISBN13,My Rating,Date Read,Date Added,Bookshelves,Exclusive Shelf,My Review,Private Notes',
        'Book One,Author One,,,,,2025/12/29,,to-read,,,',
        'Book Two,Author Two,,,,,2025/12/30,,read,,,',
    ]));

    (new StartGoodreadsImport($import->id))->handle();

    $import->refresh();

    expect($import->status)->toBe(GoodreadsImportStatus::Processing)
        ->and($import->total_rows)->toBe(2);

    Bus::assertBatched(fn ($batch) => count($batch->jobs) === 2);
});

test('it marks the import as failed when required headers are missing', function () {
    Storage::fake();

    $user = User::factory()->create();
    $import = GoodreadsImport::factory()->for($user)->create([
        'file_path' => 'goodreads-imports/test-invalid.csv',
    ]);

    Storage::put($import->file_path, implode("\n", [
        'Title,Author',
        'Book One,Author One',
    ]));

    (new StartGoodreadsImport($import->id))->handle();

    $import->refresh();

    expect($import->status)->toBe(GoodreadsImportStatus::Failed)
        ->and($import->error_message)->toContain('Header row is missing required Goodreads columns');
});
