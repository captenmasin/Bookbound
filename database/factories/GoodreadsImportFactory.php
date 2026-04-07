<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\GoodreadsImport;
use App\Enums\GoodreadsImportStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoodreadsImport>
 */
class GoodreadsImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'source' => 'goodreads',
            'status' => GoodreadsImportStatus::Pending,
            'original_filename' => 'goodreads_library_export.csv',
            'file_path' => 'goodreads-imports/test.csv',
            'batch_id' => null,
            'total_rows' => 10,
            'processed_rows' => 0,
            'imported_rows' => 0,
            'merged_rows' => 0,
            'skipped_rows' => 0,
            'failed_rows' => 0,
            'blocked_rows' => 0,
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
            'failed_at' => null,
        ];
    }
}
