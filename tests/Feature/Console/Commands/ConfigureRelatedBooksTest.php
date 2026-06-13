<?php

use App\Console\Commands\ConfigureRelatedBooks;

test('it skips Meilisearch configuration when Scout uses the database driver', function () {
    config(['scout.driver' => 'database']);

    $this->artisan(ConfigureRelatedBooks::class)
        ->expectsOutput('Skipping related books search configuration because Scout is not using Meilisearch.')
        ->assertSuccessful();
});
