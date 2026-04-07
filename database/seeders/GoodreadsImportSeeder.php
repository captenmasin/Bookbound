<?php

namespace Database\Seeders;

use App\Models\GoodreadsImport;
use Illuminate\Database\Seeder;
use App\Enums\GoodreadsImportStatus;
use Illuminate\Database\Eloquent\Factories\Sequence;

class GoodreadsImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GoodreadsImport::factory()
            ->count(3)
            ->state(new Sequence(
                ['status' => GoodreadsImportStatus::Completed, 'processed_rows' => 12, 'imported_rows' => 10, 'merged_rows' => 2, 'completed_at' => now()],
                ['status' => GoodreadsImportStatus::Processing, 'processed_rows' => 5, 'total_rows' => 18, 'started_at' => now()->subMinute()],
                ['status' => GoodreadsImportStatus::Failed, 'failed_rows' => 1, 'processed_rows' => 1, 'error_message' => 'Header row is missing required Goodreads columns.', 'failed_at' => now()]
            ))
            ->create();
    }
}
