<?php

namespace App\Models;

use App\Jobs\ImportBookCover;
use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'authors' => $this->authors()->get()->pluck('name')->toArray(),
            'tags' => $this->tags()->get()->pluck('name')->toArray(),
            'identifier' => $this->identifier,
            'path' => $this->path,
        ];
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
        return $this->covers->where('is_primary', true)->first();
    }

    public function updateColour(): void
    {
        $cover = $this->primaryCover()?->getFirstMedia('image');
        $colour = $cover ? getDominantColour($cover->getPath()) : '#000000';
        $this->settings()->set('colour', $colour);
    }

    public function relatedBooksBySearch(int $limit = 6)
    {
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

    public function relatedBooksByAuthorsAndTags(int $limit = 6)
    {
        return Cache::remember('related_books_'.$this->id, now()->addHours(6), function () use ($limit) {
            $this->loadMissing(['authors', 'tags']);

            $authorIds = $this->authors->pluck('id');
            $tagIds = $this->tags->pluck('id');

            // Load all other books with authors and tags, excluding same title
            $candidates = Book::with(['authors', 'tags'])
                ->where('id', '!=', $this->id)
                ->where('title', '!=', $this->title)
                ->get();

            $scored = $candidates->map(function ($book) use ($authorIds, $tagIds) {
                $score = 0;

                // Score for shared authors
                $sharedAuthors = $book->authors->pluck('id')->intersect($authorIds);
                $score += $sharedAuthors->count() * 5;

                // Score for shared tags
                $sharedTags = $book->tags->pluck('id')->intersect($tagIds);
                $score += $sharedTags->count() * 2;

                return [
                    'book' => $book,
                    'score' => $score,
                ];
            })->filter(fn ($entry) => $entry['score'] > 0)
                ->sortByDesc('score')
                ->take($limit)
                ->pluck('book')
                ->values(); // Re-index

            return $scored;
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
        return $this->hasMany(Cover::class)
            ->when(
                Auth::check(),
                fn ($query) => $query->where('user_id', Auth::id()
                ));
    }

    public function getPrimaryCoverAttribute(): string
    {
        $primaryCover = $this->primaryCover();
        $primaryCoverImagePath = $primaryCover?->getFirstMediaPath('image');

        if (
            ! $this->primaryCover()?->hasMedia('image')
            || ! File::exists($primaryCoverImagePath)
        ) {
            $url = $this->original_cover ??
                Vite::asset('resources/images/default-cover.svg');

            ImportBookCover::dispatchAfterResponse($this, $url);

            return $url;
        }

        return $primaryCover->image;
    }
}
