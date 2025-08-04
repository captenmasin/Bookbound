<?php

namespace App\Actions;

use App\Models\Book;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class HandlePwaProtocol
{
    use AsAction;

    public function handle(Request $request, string $type, $data): RedirectResponse
    {
        $inputData = str_replace(' ', '+', $data);
        $data = Str::after($inputData, '//');

        return match ($type) {
            'book' => $this->getBook($data),
            'search' => $this->getSearch($data),
        };
    }

    private function getBook(string $identifier): RedirectResponse
    {
        $book = Book::where('identifier', $identifier)->firstOrFail();

        return redirect()->route('books.show', $book);
    }

    private function getSearch(string $term): RedirectResponse
    {
        return redirect()->route('books.search', ['q' => $term]);
    }
}
