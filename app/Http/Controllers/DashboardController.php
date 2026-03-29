<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Inertia\Inertia;
use App\Models\Author;
use Illuminate\Http\Request;
use App\Enums\UserBookStatus;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TagResource;
use App\Http\Resources\BookResource;
use App\Http\Resources\AuthorResource;
use App\Http\Resources\ActivityResource;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->user()->load('activities');

        $books = $request->user()->books()
            ->with(['authors', 'tags'])
            ->withPivot('status', 'created_at')
            ->get();

        $books = $books->sortByDesc(fn ($book) => $book->pivot->created_at)
            ->values();

        $booksByStatus = $books->groupBy(fn ($book) => $book->pivot->status);

        $topTagNames = $books->flatMap(fn ($book) => $book->tags->pluck('name'))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(10);

        $tags = Tag::whereIn('name', $topTagNames)
            ->get()->sortBy(fn ($tag) => $topTagNames->search($tag->name))->values();

        $authors = Author::query()
            ->select('authors.*', DB::raw('count(*) as book_count'))
            ->join('author_book', 'authors.id', '=', 'author_book.author_id')
            ->join('book_user', 'author_book.book_id', '=', 'book_user.book_id')
            ->where('book_user.user_id', $request->user()->id)
            ->where('book_user.status', UserBookStatus::Read->value)
            ->groupBy('authors.id')
            ->orderByDesc('book_count')
            ->limit(5)
            ->get();

        $currentlyReading = collect([
            ...$booksByStatus[UserBookStatus::Reading->value] ?? [],
            //            ...$booksByStatus[UserBookStatus::OnHold->value] ?? [],
            //            ...$booksByStatus[UserBookStatus::Dropped->value] ?? [],
        ])->take(4);

        $completedBooks = $booksByStatus[UserBookStatus::Read->value] ?? collect();
        $planToReadBooks = $booksByStatus[UserBookStatus::PlanToRead->value] ?? collect();
        $readingBooks = $booksByStatus[UserBookStatus::Reading->value] ?? collect();
        $booksInLibrary = $books->count();
        $completedBooksCount = $completedBooks->count();
        $completedPages = (int) $completedBooks->sum('page_count');
        $totalPagesOwned = (int) $books->sum('page_count');
        $recentAddsLast30Days = $books
            ->filter(fn ($book) => $book->pivot->created_at && $book->pivot->created_at->greaterThanOrEqualTo(now()->subDays(30)))
            ->count();
        $completionRate = $booksInLibrary > 0
            ? (int) round(($completedBooksCount / $booksInLibrary) * 100)
            : 0;

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
            'insights' => [
                'totalPagesOwned' => $totalPagesOwned,
                'completedPages' => $completedPages,
                'recentAddsLast30Days' => $recentAddsLast30Days,
                'completionRate' => $completionRate,
            ],
            'activities' => ActivityResource::collection(
                $request->user()->activities->sortByDesc('id')->take(5)
            ),
            'tags' => TagResource::collection(
                $tags
            ),
            'authors' => AuthorResource::collection(
                $authors
            ),
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
            ],
        ])->withMeta([
            'title' => 'Dashboard',
        ]);
    }
}
