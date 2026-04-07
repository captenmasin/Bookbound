<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodreadsImportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalRows = (int) $this->total_rows;
        $processedRows = (int) $this->processed_rows;

        return [
            'id' => $this->id,
            'source' => $this->source,
            'status' => $this->status?->value ?? (string) $this->status,
            'original_filename' => $this->original_filename,
            'batch_id' => $this->batch_id,
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'imported_rows' => (int) $this->imported_rows,
            'merged_rows' => (int) $this->merged_rows,
            'skipped_rows' => (int) $this->skipped_rows,
            'failed_rows' => (int) $this->failed_rows,
            'blocked_rows' => (int) $this->blocked_rows,
            'error_message' => $this->error_message,
            'progress_percent' => $totalRows > 0 ? (int) floor(($processedRows / $totalRows) * 100) : 0,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'failures' => GoodreadsImportFailureResource::collection($this->whenLoaded('failures')),
            'links' => [
                'show' => route('user.books.imports.show', $this->resource),
            ],
        ];
    }
}
