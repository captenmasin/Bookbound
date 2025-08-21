<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Vite;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cover extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected static $unguarded = true;

    protected $with = ['media'];

    protected function image(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->hasMedia('image') ?
                $this->getFirstMediaUrl('image')
                : Vite::asset('resources/images/default-cover.svg');
        });
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }
}
