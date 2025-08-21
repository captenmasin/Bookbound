<?php

namespace App\Actions\Books;

use Exception;
use App\Models\Book;
use App\Models\User;
use App\Actions\TrackEvent;
use App\Enums\ActivityType;
use App\Enums\AnalyticsEvent;
use App\Enums\UserBookStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Requests\Books\UpdateBookUserRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateUserBookStatus
{
    use AsAction;

    public function handle(User $user, Book $book, UserBookStatus $status): bool
    {
        $updated = $user->books()
            ->updateExistingPivot($book->id, ['status' => $status]);

        if (! $updated) {
            throw new ModelNotFoundException('Book not found in your library.');
        }

        TrackEvent::dispatchAfterResponse(AnalyticsEvent::BookStatusUpdated, [
            'user_id' => $user->id,
            'book' => [
                'identifier' => $book->identifier,
                'title' => $book->title,
                'status' => $status->value,
            ],
        ]);

        $user->logActivity(ActivityType::BookStatusUpdated, $book, [
            'book_identifier' => $book->identifier,
            'book_title' => $book->title,
            'status' => $status,
        ]);

        return (bool) $updated;
    }

    public function asController(UpdateBookUserRequest $request, Book $book): JsonResponse|RedirectResponse
    {
        try {
            $this->handle(
                $request->user(),
                $book,
                $request->enum('status', UserBookStatus::class, default: UserBookStatus::PlanToRead)
            );

            return $request->wantsJson()
                ? response()->json([
                    'success' => true,
                    'message' => 'Book status updated successfully.',
                    'status' => $request->enum('status', UserBookStatus::class),
                ])
                : redirect()->back()->with('success', 'Book status updated successfully.');

        } catch (Exception $e) {
            return $request->wantsJson()
                ? response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 404)
                : redirect()->back()->with('error', $e->getMessage());
        }
    }
}
