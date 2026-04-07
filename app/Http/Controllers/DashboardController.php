<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Enums\UserBookStatus;
use App\Http\Resources\TagResource;
use App\Http\Resources\BookResource;
use App\Http\Resources\AuthorResource;
use App\Http\Resources\ActivityResource;
use App\Services\DashboardWeatherResolver;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->user()->load('activities');

        $books = $request->user()->books()
            ->with(['authors', 'categories', 'tags', 'notes'])
            ->withPivot('status', 'created_at')
            ->get();

        $books = $books->sortByDesc(fn ($book) => $book->pivot->created_at)
            ->values();

        $booksByStatus = $books->groupBy(fn ($book) => $book->pivot->status);

        $currentlyReading = collect([
            ...$booksByStatus[UserBookStatus::Reading->value] ?? [],
        ])->take(3);

        $completedBooks = $booksByStatus[UserBookStatus::Read->value] ?? collect();
        $planToReadBooks = $booksByStatus[UserBookStatus::PlanToRead->value] ?? collect();
        $readingBooks = $booksByStatus[UserBookStatus::Reading->value] ?? collect();
        $droppedBooks = $booksByStatus[UserBookStatus::Dropped->value] ?? collect();
        $booksInLibrary = $books->count();
        $completedBooksCount = $completedBooks->count();
        $droppedBooksCount = $droppedBooks->count();

        return Inertia::render('Dashboard', [
            'statValues' => [
                'booksInLibrary' => $booksInLibrary,
                'completedBooks' => $completedBooksCount,
                'readingBooks' => $readingBooks->count() ?? 0,
                'planToRead' => $planToReadBooks->count() ?? 0,
            ],
            'currentlyReading' => BookResource::collection(
                $currentlyReading
            ),
            'recentlyAdded' => BookResource::collection(
                $books->take(3)
            ),
            'recommendations' => Inertia::defer(fn () => $request->user()->getRecommendations()),
            'insights' => [
                'read' => $booksInLibrary > 0
                    ? round(($completedBooksCount / $booksInLibrary) * 100, 2)
                    : 0,
                'dropped' => $booksInLibrary > 0
                    ? round(($droppedBooksCount / $booksInLibrary) * 100, 2)
                    : 0,
            ],
            'activities' => ActivityResource::collection(
                $request->user()->activities->sortByDesc('id')->take(5)
            ),
            'tags' => TagResource::collection($request->user()->getTags()),
            'authors' => AuthorResource::collection($request->user()->getAuthors()->get()),
            'topGenres' => $request->user()->getTopGenres(),
            'weather' => Inertia::defer(fn () => new DashboardWeatherResolver()->resolve($request)),
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
            ],
        ])->withMeta([
            'title' => 'Dashboard',
        ]);
    }
}
