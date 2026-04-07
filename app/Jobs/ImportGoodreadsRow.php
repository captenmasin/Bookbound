<?php

namespace App\Jobs;

use Throwable;
use App\Models\Book;
use App\Models\Note;
use App\Models\User;
use App\Models\Rating;
use App\Models\Review;
use Illuminate\Support\Arr;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Carbon;
use App\Models\GoodreadsImport;
use Illuminate\Support\Facades\DB;
use App\Support\SubscriptionLimits;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Support\Goodreads\GoodreadsBookMatcher;
use App\Support\Goodreads\GoodreadsRowNormalizer;

class ImportGoodreadsRow implements ShouldQueue
{
    use Batchable, Queueable;

    public int $timeout = 120;

    /**
     * @param  array<string, mixed>  $row
     */
    public function __construct(
        public int $goodreadsImportId,
        public int $rowNumber,
        public array $row
    ) {}

    public function handle(
        GoodreadsRowNormalizer $normalizer,
        GoodreadsBookMatcher $matcher
    ): void {
        $import = GoodreadsImport::query()
            ->with('user')
            ->findOrFail($this->goodreadsImportId);

        try {
            $normalized = $normalizer->normalize($this->row);
            $book = $matcher->match($normalized);

            if (! $book) {
                $this->recordSkip($import, $normalized, 'No credible title and author match was found for this row.');

                return;
            }

            $outcome = DB::transaction(function () use ($import, $normalized, $book) {
                $user = User::query()->whereKey($import->user_id)->lockForUpdate()->firstOrFail();
                $alreadyInLibrary = $user->books()->where('book_id', $book->id)->exists();

                if ($alreadyInLibrary) {
                    $this->mergeExistingLibraryEntry($user, $book, $normalized);
                    $outcome = 'merged';
                } else {
                    if (! SubscriptionLimits::canAddBook($user)) {
                        return 'blocked';
                    }

                    $this->attachNewLibraryEntry($user, $book, $normalized);
                    $outcome = 'imported';
                }

                $this->upsertRating($import, $book, $normalized);
                $this->upsertReview($import, $book, $normalized);
                $this->createPrivateNote($import, $book, $normalized);

                return $outcome;
            });

            if ($outcome === 'blocked') {
                $this->recordBlocked($import, $normalized, 'Book skipped because this import would exceed the current plan book limit.');

                return;
            }

            if ($outcome === 'merged') {
                $import->increment('merged_rows');
            } else {
                $import->increment('imported_rows');
            }

        } catch (Throwable $exception) {
            $this->recordFailure($import, $this->row, $exception->getMessage());
            $import->increment('failed_rows');
        } finally {
            $import->increment('processed_rows');
        }
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    protected function attachNewLibraryEntry(User $user, Book $book, array $normalized): void
    {
        $user->books()->attach($book->id, [
            'status' => $normalized['status'],
            'tags' => $normalized['user_tags'],
            'read_at' => $normalized['read_at'],
            'created_at' => $normalized['added_at'] ?? now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    protected function mergeExistingLibraryEntry(User $user, Book $book, array $normalized): void
    {
        $pivot = $user->books()
            ->where('book_id', $book->id)
            ->firstOrFail()
            ->pivot;

        $mergedTags = collect($pivot->tags ?? [])
            ->merge($normalized['user_tags'])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $existingCreatedAt = $pivot->created_at?->toDateTimeString();
        $incomingCreatedAt = $normalized['added_at'];
        $createdAt = $existingCreatedAt;

        if ($existingCreatedAt && $incomingCreatedAt) {
            $createdAt = Carbon::parse($existingCreatedAt)->lte(Carbon::parse($incomingCreatedAt))
                ? $existingCreatedAt
                : $incomingCreatedAt;
        } elseif (! $existingCreatedAt && $incomingCreatedAt) {
            $createdAt = $incomingCreatedAt;
        }

        $user->books()->updateExistingPivot($book->id, [
            'status' => $normalized['status'],
            'tags' => $mergedTags,
            'read_at' => $normalized['read_at'] ?? $pivot->read_at?->toDateTimeString(),
            'created_at' => $createdAt ?? now()->toDateTimeString(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    protected function upsertRating(GoodreadsImport $import, Book $book, array $normalized): void
    {
        if (! $normalized['rating']) {
            return;
        }

        Rating::query()->updateOrCreate(
            [
                'book_id' => $book->id,
                'user_id' => $import->user_id,
            ],
            [
                'value' => $normalized['rating'],
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    protected function upsertReview(GoodreadsImport $import, Book $book, array $normalized): void
    {
        if (! $normalized['review']) {
            return;
        }

        Review::query()->updateOrCreate(
            [
                'book_id' => $book->id,
                'user_id' => $import->user_id,
            ],
            [
                'title' => null,
                'content' => $normalized['review'],
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    protected function createPrivateNote(GoodreadsImport $import, Book $book, array $normalized): void
    {
        if (! $normalized['private_notes']) {
            return;
        }

        $exists = Note::query()
            ->where('book_id', $book->id)
            ->where('user_id', $import->user_id)
            ->where('content', $normalized['private_notes'])
            ->exists();

        if ($exists) {
            return;
        }

        Note::query()->create([
            'book_id' => $book->id,
            'user_id' => $import->user_id,
            'book_status' => $normalized['status'],
            'content' => $normalized['private_notes'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    protected function recordSkip(GoodreadsImport $import, array $normalized, string $reason): void
    {
        $this->recordFailure($import, $normalized['raw_row'], $reason, $normalized['title'], $normalized['primary_author']);
        $import->increment('skipped_rows');
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    protected function recordBlocked(GoodreadsImport $import, array $normalized, string $reason): void
    {
        $this->recordFailure($import, $normalized['raw_row'], $reason, $normalized['title'], $normalized['primary_author']);
        $import->increment('blocked_rows');
        $import->increment('skipped_rows');
    }

    /**
     * @param  array<string, mixed>  $rawRow
     */
    protected function recordFailure(
        GoodreadsImport $import,
        array $rawRow,
        string $reason,
        ?string $title = null,
        ?string $author = null
    ): void {
        $import->failures()->create([
            'row_number' => $this->rowNumber,
            'title' => $title ?? Arr::get($rawRow, 'Title'),
            'author' => $author ?? Arr::get($rawRow, 'Author'),
            'reason' => $reason,
            'raw_row' => $rawRow,
        ]);
    }
}
