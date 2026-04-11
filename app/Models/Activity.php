<?php

namespace App\Models;

use App\Enums\ActivityType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getDescriptionAttribute(): string
    {
        return $this->descriptionForAudience('owner');
    }

    public function publicDescription(): string
    {
        return $this->descriptionForAudience('public');
    }

    private function descriptionForAudience(string $audience): string
    {
        return Cache::remember("activity_description_{$audience}_{$this->id}", now()->addDay(), function () use ($audience) {
            $this->loadMissing(['subject', 'user']);

            $subject = $this->subject;
            $extra = $this->properties ?? [];
            $bookTitle = match (true) {
                $subject instanceof Book && $subject->title => $subject->title,
                $subject instanceof Note && $subject->book?->title => $subject->book->title,
                $subject instanceof Review && $subject->book?->title => $subject->book->title,
                default => $extra['book_title'] ?? 'Unknown Book',
            };

            $actor = $audience === 'public'
                ? ($this->user?->name ?? 'This reader')
                : 'You';

            return match ($this->type) {
                ActivityType::BookAdded->value => "{$actor} added <strong>{$bookTitle}</strong> to ".($audience === 'public' ? 'their' : 'your').' library as <em>'.($extra['status'] ?? 'unknown').'</em>.',

                ActivityType::BookStatusUpdated->value => "{$actor} updated the status of <strong>{$bookTitle}</strong> to <em>".($extra['status'] ?? 'unknown').'</em>.',

                ActivityType::BookRemoved->value => "{$actor} removed <strong>{$bookTitle}</strong> from ".($audience === 'public' ? 'their' : 'your').' library.',

                ActivityType::BookNoteAdded->value => "{$actor} added a note to <strong>{$bookTitle}</strong>.",

                ActivityType::BookNoteUpdated->value => "{$actor} updated ".($audience === 'public' ? 'their' : 'your')." note on <strong>{$bookTitle}</strong>.",

                ActivityType::BookNoteRemoved->value => "{$actor} removed ".($audience === 'public' ? 'their' : 'your')." note from <strong>{$bookTitle}</strong>.",

                ActivityType::BookReviewAdded->value => "{$actor} added a review for <strong>{$bookTitle}</strong> ".(! empty($extra['rating']) ? ('&mdash; '.$extra['rating'].' stars') : '').'.',

                ActivityType::BookReviewUpdated->value => "{$actor} updated ".($audience === 'public' ? 'their' : 'your')." review for <strong>{$bookTitle}</strong>".(! empty($extra['rating']) ? ('&mdash; '.$extra['rating'].' stars') : '').'.',

                ActivityType::BookReviewRemoved->value => "{$actor} removed ".($audience === 'public' ? 'their' : 'your')." review from <strong>{$bookTitle}</strong>.",

                ActivityType::BookCoverUpdated->value => "{$actor} updated the cover for <strong>{$bookTitle}</strong>.",

                ActivityType::BookCoverRemoved->value => "{$actor} removed the cover for <strong>{$bookTitle}</strong>.",

                default => $audience === 'public' ? 'This reader did something.' : 'You did something.',
            };
        });
    }
}
