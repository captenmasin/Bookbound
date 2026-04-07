<?php

namespace App\Models;

use App\Enums\GoodreadsImportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Database\Factories\GoodreadsImportFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodreadsImport extends Model
{
    /** @use HasFactory<GoodreadsImportFactory> */
    use HasFactory;

    protected static $unguarded = true;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'status' => GoodreadsImportStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function failures(): HasMany
    {
        return $this->hasMany(GoodreadsImportFailure::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            GoodreadsImportStatus::Pending->value,
            GoodreadsImportStatus::Processing->value,
        ]);
    }
}
