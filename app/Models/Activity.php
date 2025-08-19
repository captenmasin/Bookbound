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
        return Cache::remember('activity_description_'.$this->id, now()->addDay(), function () {
            $this->load('subject');
            $subject = $this->subject;
            $extra = $this->properties ?? [];

            if ($subject instanceof Book && $subject->title) {
                $bookTitle = $subject->title;
            } elseif ($subject instanceof Note && $subject->book?->title) {
                $bookTitle = $subject->book->title;
            } elseif ($subject instanceof Review && $subject->book?->title) {
                $bookTitle = $subject->book->title;
            } else {
                $bookTitle = $extra['book_title'] ?? null;
            }

            if ($bookTitle === null) {
                $bookTitle = 'Unknown Book';
            }

            return match ($this->type) {
                ActivityType::BookAdded->value => "You added <strong>{$bookTitle}</strong> to your library as <em>".($extra['status'] ?? 'unknown').'</em>.',

                ActivityType::BookStatusUpdated->value => "You updated the status of <strong>{$bookTitle}</strong> to <em>".($extra['status'] ?? 'unknown').'</em>.',

                ActivityType::BookRemoved->value => "You removed <strong>{$bookTitle}</strong> from your library.",

                ActivityType::BookNoteAdded->value => "You added a note to <strong>{$bookTitle}</strong>.",

                ActivityType::BookNoteUpdated->value => "You updated your note on <strong>{$bookTitle}</strong>.",

                ActivityType::BookNoteRemoved->value => "You removed your note from <strong>{$bookTitle}</strong>.",

                ActivityType::BookReviewAdded->value => "You added a review for <strong>{$bookTitle}</strong> ".(! empty($extra['rating']) ? ('&mdash; '.$extra['rating'].' stars') : '').'.',

                ActivityType::BookReviewUpdated->value => "You updated your review for <strong>{$bookTitle}</strong>".(! empty($extra['rating']) ? ('&mdash; '.$extra['rating'].' stars') : '').'.',

                ActivityType::BookReviewRemoved->value => "You removed your review from <strong>{$bookTitle}</strong>.",

                ActivityType::BookCoverUpdated->value => "You updated the cover for <strong>{$bookTitle}</strong>.",

                ActivityType::BookCoverRemoved->value => "You removed the cover for <strong>{$bookTitle}</strong>.",

                default => 'You did something.',
            };
        });
    }
}
