<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\InteractsWithMedia;
use Glorand\Model\Settings\Traits\HasSettingsField;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model implements HasMedia
{
    use HasFactory, HasSettingsField, HasSlug, InteractsWithMedia, Searchable;

    protected static $unguarded = true;

    protected $with = [];

    protected function casts(): array
    {
        return [
            'codes' => 'array',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['title', 'identifier'])
            ->saveSlugsTo('path');
    }

    public function getRouteKeyName(): string
    {
        return 'path';
    }

    public function searchableAs(): string
    {
        return app()->environment().'_books';
    }

    public function toSearchableArray(): array
    {
        $array = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'identifier' => $this->identifier,
            'path' => $this->path,
        ];

        if (config('scout.driver') !== 'database') {
            $array['authors'] = $this->authors()->get()->pluck('name')->toArray();
            $array['tags'] = $this->tags()->get()->pluck('name')->toArray();
        }

        return $array;
    }

    protected static function booted(): void
    {
        self::deleted(static function (Book $book): void {
            $book->covers()->delete();
        });
    }

    public function getUserStatus(User $user)
    {
        return $this->users()
            ->where('user_id', $user->id)
            ->first()?->pivot?->status ?? null;
    }

    public function primaryCover()
    {
        $exists = $this->covers()->where('is_primary', true)->exists();

        if ($exists) {
            return $this->covers()->where('is_primary', true)->first();
        }

        return $this->covers()->create(['is_primary' => true]);
    }

    public function updateColour(): void
    {
        $cover = $this->primaryCover()?->getFirstMedia('image');
        $colour = $cover ? getDominantColourFromMedia($cover) : '#000000';
        $this->settings()->set('colour', $colour);
    }

    public function relatedBooksBySearch(int $limit = 6): Collection
    {
        if (config('scout.driver') === 'database') {
            return $this->relatedBooksByAuthorsAndTags($limit);
        }

        $authors = $this->authors->pluck('name')->toArray();
        $tags = $this->tags->pluck('name')->toArray();

        $searchTerms = array_merge($authors, $tags);
        $query = implode(' ', $searchTerms);

        return Book::search($query)
            ->take($limit * 2)
            ->get()
            ->filter(fn ($book) => $book->title !== $this->title && $book->id !== $this->id)
            ->take($limit)
            ->values();
    }

    public function relatedBooksByAuthorsAndTags(int $limit = 6): Collection
    {
        if ($limit < 1) {
            return collect();
        }

        return Cache::remember('related_books_v2_'.$this->id.'_limit_'.$limit, now()->addHours(6), function () use ($limit): Collection {
            $this->loadMissing(['authors', 'tags']);

            $authorIds = $this->authors->pluck('id')->values();
            $tagIds = $this->tags->pluck('id')->values();

            if ($authorIds->isEmpty() && $tagIds->isEmpty()) {
                return collect();
            }

            $query = Book::query()
                ->select('books.*')
                ->where('books.id', '!=', $this->id)
                ->where('books.title', '!=', $this->title)
                ->where(function ($query) use ($authorIds, $tagIds): void {
                    if ($authorIds->isNotEmpty()) {
                        $query->whereHas('authors', fn ($query) => $query->whereIn('authors.id', $authorIds));
                    }

                    if ($tagIds->isNotEmpty()) {
                        $method = $authorIds->isNotEmpty() ? 'orWhereHas' : 'whereHas';

                        $query->{$method}('tags', fn ($query) => $query->whereIn('tags.id', $tagIds));
                    }
                })
                ->with(['authors', 'tags'])
                ->groupBy('books.id')
                ->limit($limit);

            if ($authorIds->isNotEmpty()) {
                $query->leftJoin('author_book as matching_authors', function ($join) use ($authorIds): void {
                    $join->on('books.id', '=', 'matching_authors.book_id')
                        ->whereIn('matching_authors.author_id', $authorIds);
                });
            }

            if ($tagIds->isNotEmpty()) {
                $query->leftJoin('book_tag as matching_tags', function ($join) use ($tagIds): void {
                    $join->on('books.id', '=', 'matching_tags.book_id')
                        ->whereIn('matching_tags.tag_id', $tagIds);
                });
            }

            $authorScoreSql = $authorIds->isNotEmpty()
                ? 'count(distinct matching_authors.author_id) * 5'
                : '0';

            $tagScoreSql = $tagIds->isNotEmpty()
                ? 'count(distinct matching_tags.tag_id) * 2'
                : '0';

            return $query
                ->orderByRaw("({$authorScoreSql} + {$tagScoreSql}) desc")
                ->orderBy('title')
                ->get()
                ->values();
        });
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(BookUser::class)
            ->withPivot(['status', 'tags', 'created_at', 'updated_at']);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function averageRating(): float|int|null
    {
        return $this->reviews()->avg('rating');
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function covers(): HasMany
    {
        return $this->hasMany(Cover::class);
    }

    public function getPrimaryCoverAttribute(): string
    {
        $primaryCover = $this->primaryCover();
        $media = $primaryCover?->getFirstMedia('image');

        if (
            ! $media
            || ! Storage::disk($media->disk)->exists($media->getPathRelativeToRoot())
        ) {
            return $this->original_cover ??
                Vite::asset('resources/images/default-cover.svg');
        }

        return $primaryCover->image;
    }
}
