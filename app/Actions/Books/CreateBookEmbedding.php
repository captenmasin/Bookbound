<?php

namespace App\Actions\Books;

use App\Models\Book;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

use function Laravel\Prompts\spin;

use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateBookEmbedding
{
    use AsAction;

    public $commandSignature = 'book:embeddings {--fresh}';

    public function handle(Book|Collection|array $books): void
    {
        $books = match (true) {
            $books instanceof Book => collect([$books]),
            $books instanceof Collection => $books,
            default => collect($books),
        };

        foreach ($books as $book) {
            $embeddingContent =
                $book->title.' '.
                $book->authors->implode('name', ', ').' '.
                ($book->publisher?->name ?? '').' '.
                $book->description.' '.$book->tags->implode('name', ', ');

            $book->updateQuietly([
                'embedding' => Str::of(strtolower(strip_tags($embeddingContent)))->toEmbeddings(),
            ]);
        }
    }

    public function asCommand(Command $command): void
    {
        $fresh = $command->option('fresh');
        if ($fresh) {
            $books = Book::all();
        } else {
            $books = Book::whereNull('embedding')->get();
        }

        $generating = spin(
            callback: fn () => $this->handle($books),
            message: 'Generating response...'
        );

        $command->info(count($books).' embeddings generated');
    }
}
