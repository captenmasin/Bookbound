<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Actions\TrackEvent;
use App\Enums\ActivityType;
use Illuminate\Http\Request;
use App\Enums\AnalyticsEvent;
use Illuminate\Routing\Controller;
use App\Support\SubscriptionLimits;
use App\Http\Requests\UpdateBookCoverRequest;

class BookCoverController extends Controller
{
    public function update(UpdateBookCoverRequest $request, Book $book)
    {
        if (! SubscriptionLimits::allowCustomCovers($request->user())) {
            return back()->with('error', 'Your current plan does not allow adding custom covers.');
        }

        // Validate via dedicated Form Request (sends errors to 'bookCoverBag')
        $request->validated();

        if ($request->file('cover')) {
            $newCover = $book->covers()->create([
                'user_id' => $request->user()->id,
            ]);

            $newCover->addMedia($request->file('cover'))->toMediaCollection('image');

            TrackEvent::dispatchAfterResponse(AnalyticsEvent::BookCoverUpdated, [
                'user_id' => $request->user()?->id,
                'book' => [
                    'book_identifier' => $book->identifier,
                    'book_title' => $book->title,
                ],
            ]);

            $request->user()->logActivity(
                ActivityType::BookCoverUpdated,
                $newCover,
                [
                    'book_identifier' => $book->identifier,
                    'book_title' => $book->title,
                ]
            );
        }

        return redirect()->back()->with('success', __('Book cover updated successfully.'));
    }

    public function destroy(Request $request, Book $book)
    {
        TrackEvent::dispatchAfterResponse(AnalyticsEvent::BookCoverRemoved, [
            'user_id' => $request->user()?->id,
            'book' => [
                'book_identifier' => $book->identifier,
                'book_title' => $book->title,
            ],
        ]);

        $request->user()->logActivity(
            ActivityType::BookCoverRemoved,
            null,
            [
                'book_identifier' => $book->identifier,
                'book_title' => $book->title,
            ]
        );

        $request->user()->book_covers()->where('book_id', $book->id)->delete();
    }
}
