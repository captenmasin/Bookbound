<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\GoodreadsImport;
use App\Jobs\StartGoodreadsImport;
use App\Enums\GoodreadsImportStatus;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\GoodreadsImportResource;
use App\Http\Requests\GoodreadsImportStoreRequest;

class GoodreadsImportController extends Controller
{
    public function create(): Response
    {
        $user = request()->user();
        $activeImport = $user->goodreadsImports()
            ->active()
            ->latest()
            ->with('failures')
            ->first();

        $recentImports = $user->goodreadsImports()
            ->latest()
            ->with('failures')
            ->limit(10)
            ->get();

        return Inertia::render('books/ImportGoodreads', [
            'activeImport' => $activeImport ? GoodreadsImportResource::make($activeImport) : null,
            'recentImports' => GoodreadsImportResource::collection($recentImports),
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Books', 'href' => route('user.books.index')],
                ['title' => 'Import Goodreads', 'href' => route('user.books.imports.create')],
            ],
        ])->withMeta([
            'title' => 'Import Goodreads Library',
            'description' => 'Upload a Goodreads CSV export and merge it into your Bookbound library.',
        ]);
    }

    public function store(GoodreadsImportStoreRequest $request): RedirectResponse
    {
        $user = $request->user();

        $activeImport = $user->goodreadsImports()
            ->active()
            ->latest()
            ->first();

        if ($activeImport) {
            return redirect()
                ->route('user.books.imports.show', $activeImport)
                ->with('error', 'You already have an active Goodreads import in progress.');
        }

        $storedFile = $request->file('file')->store('goodreads-imports/'.$user->id);

        $import = $user->goodreadsImports()->create([
            'source' => 'goodreads',
            'status' => GoodreadsImportStatus::Pending,
            'original_filename' => $request->file('file')->getClientOriginalName(),
            'file_path' => $storedFile,
        ]);

        StartGoodreadsImport::dispatch($import->id)->onQueue('imports');

        return redirect()
            ->route('user.books.imports.show', $import)
            ->with('success', 'Goodreads import queued successfully.');
    }

    public function show(GoodreadsImport $goodreadsImport): Response
    {
        abort_unless($goodreadsImport->user_id === request()->user()->id, 404);

        $goodreadsImport->load([
            'failures' => fn ($query) => $query->latest()->limit(20),
        ]);

        return Inertia::render('books/GoodreadsImportShow', [
            'importRecord' => GoodreadsImportResource::make($goodreadsImport),
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Books', 'href' => route('user.books.index')],
                ['title' => 'Import Goodreads', 'href' => route('user.books.imports.create')],
                ['title' => 'Import Status', 'href' => route('user.books.imports.show', $goodreadsImport)],
            ],
        ])->withMeta([
            'title' => 'Import Status',
            'description' => 'Track the progress of your Goodreads import.',
        ]);
    }
}
