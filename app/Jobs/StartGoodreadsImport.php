<?php

namespace App\Jobs;

use Throwable;
use Illuminate\Bus\Batch;
use App\Models\GoodreadsImport;
use Illuminate\Support\Facades\Bus;
use App\Enums\GoodreadsImportStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Support\Goodreads\GoodreadsRowNormalizer;

class StartGoodreadsImport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public function __construct(
        public int $goodreadsImportId
    ) {}

    public function handle(): void
    {
        $import = GoodreadsImport::query()->findOrFail($this->goodreadsImportId);

        $import->update([
            'status' => GoodreadsImportStatus::Processing,
            'started_at' => now(),
            'error_message' => null,
        ]);

        if (! Storage::exists($import->file_path)) {
            $this->failImport($import, 'Import file could not be found.');

            return;
        }

        $stream = fopen(Storage::path($import->file_path), 'rb');

        if ($stream === false) {
            $this->failImport($import, 'Import file could not be opened.');

            return;
        }

        try {
            $header = fgetcsv($stream);
            $header = GoodreadsRowNormalizer::normalizeHeader($header ?: []);
            GoodreadsRowNormalizer::assertRequiredHeaders($header);

            $batch = null;
            $jobs = [];
            $rowNumber = 1;
            $totalRows = 0;

            while (($row = fgetcsv($stream)) !== false) {
                $rowNumber++;

                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $jobs[] = new ImportGoodreadsRow(
                    goodreadsImportId: $import->id,
                    rowNumber: $rowNumber,
                    row: array_combine($header, $row) ?: []
                );

                if (count($jobs) === 50) {
                    $totalRows += count($jobs);
                    $batch = $this->dispatchOrAppendBatch($batch, $import->id, $jobs);
                    $jobs = [];
                }
            }

            if ($jobs !== []) {
                $totalRows += count($jobs);
                $batch = $this->dispatchOrAppendBatch($batch, $import->id, $jobs);
            }

            if (! $batch) {
                $this->failImport($import, 'The uploaded file does not contain any Goodreads rows.');

                return;
            }

            $import->update([
                'batch_id' => $batch->id,
                'total_rows' => $totalRows,
            ]);
        } catch (Throwable $exception) {
            $this->failImport($import, $exception->getMessage());
        } finally {
            fclose($stream);
        }
    }

    /**
     * @param  array<int, ImportGoodreadsRow>  $jobs
     */
    protected function dispatchOrAppendBatch(?Batch $batch, int $importId, array $jobs): Batch
    {
        if ($batch) {
            $batch->add($jobs);

            return $batch;
        }

        return Bus::batch($jobs)
            ->allowFailures()
            ->name("Goodreads import {$importId}")
            ->finally(function (Batch $batch) use ($importId) {
                $import = GoodreadsImport::query()->find($importId);

                if (! $import || $import->status === GoodreadsImportStatus::Failed) {
                    return;
                }

                $import->update([
                    'status' => GoodreadsImportStatus::Completed,
                    'completed_at' => now(),
                ]);
            })
            ->onQueue('imports')
            ->dispatch();
    }

    protected function failImport(GoodreadsImport $import, string $message): void
    {
        $import->update([
            'status' => GoodreadsImportStatus::Failed,
            'error_message' => $message,
            'failed_at' => now(),
            'completed_at' => now(),
        ]);
    }

    /**
     * @param  array<int, mixed>  $row
     */
    protected function rowIsEmpty(array $row): bool
    {
        return collect($row)
            ->filter(fn ($value) => trim((string) $value) !== '')
            ->isEmpty();
    }
}
