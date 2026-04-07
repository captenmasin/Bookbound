<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\GoodreadsImportFailureFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodreadsImportFailure extends Model
{
    /** @use HasFactory<GoodreadsImportFailureFactory> */
    use HasFactory;

    protected static $unguarded = true;

    protected function casts(): array
    {
        return [
            'raw_row' => 'array',
        ];
    }

    public function goodreadsImport(): BelongsTo
    {
        return $this->belongsTo(GoodreadsImport::class);
    }
}
