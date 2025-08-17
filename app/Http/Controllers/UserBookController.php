<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Inertia\Inertia;
use App\Enums\ActivityType;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Enums\UserBookStatus;
use App\Http\Resources\TagResource;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AuthorResource;
use App\Http\Requests\Books\DestroyBookUserRequest;

class UserBookController extends Controller
{
    public function index(Request $request)
    {
        $bookQuery = Auth::user()->books()->with([
            'authors',
            'ratings',
            'users' => fn ($q) => $q->where('user_id', auth()->id()),
        ]);

        if ($request->filled('status')) {
            $statuses = Arr::wrap($request->input('status'));
            $statuses = collect($statuses)->filter(fn ($status) => in_array($status, UserBookStatus::values()))->values()->all();

            if (! empty($statuses)) {
                $bookQuery->wherePivotIn('status', $statuses);
            }
        }

        if ($request->filled('tag')) {
            $tagSlug = $request->get('tag');
            $bookQuery->whereHas('tags', function ($query) use ($tagSlug) {
                $query->where('slug', $tagSlug);
            });
        }

        if ($request->filled('search')) {
            $search = strtolower($request->get('search'));
            $bookQuery->where(function ($query) use ($search) {
                $query
                    ->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('authors', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]))
                    ->orWhereRaw('LOWER(identifier) LIKE ?', ["%{$search}%"]);
            });
        }

        $sort = in_array($request->get('sort'), ['title', 'rating', 'published_date', 'added', 'author', 'colour'])
            ? $request->get('sort')
            : null;

        $books = $bookQuery->get();
        $direction = $request->get('order', 'desc');
        $desc = $direction === 'desc';

        if ($sort === 'title') {
            $books = $bookQuery->orderBy('title', $desc ? 'desc' : 'asc')->get();
        } elseif ($sort === 'author') {
            $books = $books->sortBy(function ($book) {
                return strtolower(implode($book->authors->pluck('name')->toArray()));
            }, SORT_NATURAL | SORT_FLAG_CASE, $desc);
        } elseif ($sort === 'rating') {
            $books = $books->sortBy(function ($book) use ($request) {
                return optional($book->ratings->firstWhere('user_id', $request->user()->id))->value ?? 0;
            }, SORT_REGULAR, $desc);
        } elseif ($sort === 'published_date') {
            $books = $bookQuery->orderBy('published_date', $desc ? 'desc' : 'asc')->get();
        } elseif ($sort === 'added' || is_null($sort)) {
            $books = $books->sortBy(function ($book) {
                return $book->pivot->created_at;
            }, SORT_REGULAR, $desc);
        } elseif ($sort === 'colour') {
            $books = $books->sortBy(function ($book) {
                $hex = ltrim($book->settings()->get('colour', '#000000'), '#');

                if (strlen($hex) !== 6) {
                    return 0;
                }

                [$r, $g, $b] = [
                    hexdec(substr($hex, 0, 2)),
                    hexdec(substr($hex, 2, 2)),
                    hexdec(substr($hex, 4, 2)),
                ];

                return rgbToHue($r, $g, $b);
            }, SORT_NUMERIC, $desc);
        }

        if ($request->filled('author')) {
            $authorSlug = $request->get('author');
            $books = $books->filter(function ($book) use ($authorSlug) {
                return $book->authors->contains('slug', $authorSlug);
            });
        }

        $selectedStatuses = $request->get('status', []);

        return Inertia::render('books/Index', [
            'totalBooks' => $request->user()->books->count(),
            'books' => BookResource::collection($books),
            'selectedStatuses' => $selectedStatuses,
            'selectedAuthor' => $request->get('author'),
            'selectedTag' => $request->get('tag'),
            'selectedSort' => $sort,
            'selectedOrder' => $request->get('order', 'desc'),
            'searchQuery' => $request->get('search', ''),

            'authors' => Inertia::defer(fn () => AuthorResource::collection($request->user()->books->load('authors')->flatMap(function ($book) {
                return $book->authors;
            })->sortBy('name')->all())),

            'tags' => Inertia::defer(fn () => TagResource::collection($request->user()->books->load('tags')->flatMap(function ($book) {
                return $book->tags;
            })->unique('slug')->all())),

            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Books', 'href' => route('user.books.index')],
            ],
        ])->withMeta([
            'title' => 'Your Library',
            'description' => 'A collection of your books.',
        ]);
    }

    public function updateTags(Request $request, Book $book)
    {
        $bookUser = $request->user()->books()->where('book_id', $book->id)->first()?->pivot;
        $bookUser->tags = $request->get('tags', []);
        $bookUser->save();

        return redirect()->back()
            ->with('success', 'Tags updated successfully');
    }

    public function destroy(DestroyBookUserRequest $request, Book $book)
    {
        if (! $request->user()->books()->where('book_id', $book->id)->exists()) {
            return redirect()->back()->with('message', 'Book not found in your collection.');
        }

        $request->user()->logActivity(
            ActivityType::BookRemoved,
            $book
        );

        $request->user()->books()->detach($book);

        return redirect()->back();
    }
}
