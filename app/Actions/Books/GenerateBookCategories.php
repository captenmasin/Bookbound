<?php

namespace App\Actions\Books;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

use function Laravel\Prompts\spin;

use Illuminate\Support\Collection;
use App\Ai\Agents\BookCategoryAgent;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateBookCategories
{
    use AsAction;

    public $commandSignature = 'book:categories {--fresh}';

    public function handle(Book|Collection|array $books): void
    {
        $books = match (true) {
            $books instanceof Book => collect([$books]),
            $books instanceof Collection => $books,
            default => collect($books),
        };

        foreach ($books as $book) {
            $categoryIds = collect(BookCategoryAgent::make(book: $book)->categorize())
                ->map(fn (mixed $category): ?string => is_string($category) ? trim($category) : null)
                ->filter()
                ->unique()
                ->map(function (string $category): int {
                    return Category::query()->firstOrCreate(
                        ['slug' => Str::slug($category)],
                        ['name' => $category],
                    )->getKey();
                });

            $book->categories()->sync($categoryIds->all());
        }
    }

    public function asCommand(Command $command): void
    {
        $fresh = $command->option('fresh');
        if ($fresh) {
            $books = Book::all();
        } else {
            $books = Book::doesntHave('categories')->get();
        }

        $generating = spin(
            callback: fn () => $this->handle($books),
            message: 'Generating response...'
        );

        $command->info(count($books).' categories generated');
    }
}
