<?php

namespace App\Actions\Books;

use App\Models\Book;
use App\Models\User;
use DomainException;
use App\Actions\TrackEvent;
use App\Enums\ActivityType;
use App\Enums\AnalyticsEvent;
use App\Enums\UserBookStatus;
use Illuminate\Http\JsonResponse;
use App\Support\SubscriptionLimits;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Requests\Books\StoreBookUserRequest;

class AddBookToUser
{
    use AsAction;

    public function handle(Book $book, User $user, ?UserBookStatus $status = null): void
    {
        if ($user->books()->where('book_id', $book->id)->exists()) {
            throw new DomainException('Book already exists in your library.');
        }

        // Enforce subscription limit for maximum number of books
        if (! SubscriptionLimits::canAddBook($user)) {
            $max = SubscriptionLimits::getLimit($user, 'max_books');
            $message = 'You have reached the maximum number of books allowed by your subscription.';
            if ($max !== null) {
                $message = "You can have up to {$max} books in your library. Remove a book or upgrade your plan to add more.";
            }
            throw new DomainException($message);
        }

        TrackEvent::dispatchAfterResponse(AnalyticsEvent::BookAdded, [
            'user_id' => $user->id,
            'book' => [
                'identifier' => $book->identifier,
                'title' => $book->title,
                'status' => $status?->value ?? UserBookStatus::PlanToRead->value,
            ],
        ]);

        $user->books()->attach($book, [
            'status' => $status->value ?? UserBookStatus::PlanToRead,
        ]);

        $user->logActivity(ActivityType::BookAdded, $book, [
            'book_identifier' => $book->identifier,
            'book_title' => $book->title,
            'status' => $status->value ?? UserBookStatus::PlanToRead->value,
        ]);
    }

    public function asController(StoreBookUserRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $book = Book::where('identifier', $request->get('identifier'))->first();

            if (! $book) {
                throw new DomainException('Book not found.');
            }

            $this->handle(
                $book,
                $request->user(),
                $request->enum('status', UserBookStatus::class, default: UserBookStatus::PlanToRead)
            );

            return $request->wantsJson()
                ? response()->json([
                    'success' => true,
                    'message' => 'Book added to your library successfully.',
                ])
                : redirect()->back()->with('success', 'Book added to your library successfully.');
        } catch (\Exception $e) {
            $isExpectedException = $e instanceof DomainException;

            if (! $isExpectedException) {
                report($e);
            }

            $message = $isExpectedException
                ? $e->getMessage()
                : 'Unable to add this book to your library. Please try again.';

            return $request->wantsJson()
                ? response()->json([
                    'success' => false,
                    'message' => $message,
                ], $isExpectedException ? 400 : 500)
                : redirect()->back()->with('error', $message);
        }
    }
}
