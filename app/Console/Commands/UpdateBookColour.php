<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\multiselect;

class UpdateBookColour extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'book:colour
                            {--all : Update all books}
                            {--book=* : Specific book ID(s) to update}';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scope = select(
            label: 'Which books would you like to update?',
            options: ['all' => 'All books', 'specific' => 'Specific books'],
        );

        $query = Book::query();

        if ($scope === 'specific') {
            $books = Book::pluck('title', 'id');

            if ($books->isEmpty()) {
                error('No books found.');

                return Command::FAILURE;
            }

            $selected = multiselect(
                label: 'Select the books to update',
                options: $books,
                required: true,
            );

            $query->whereIn('id', $selected);
        }

        $books = $query->get();

        if ($books->isEmpty()) {
            warning('No books found.');

            return Command::FAILURE;
        }

        progress(
            label: "Updating colour for {$books->count()} book(s)...",
            steps: $books,
            callback: fn (Book $book) => $book->updateColour(),
        );

        info('Done!');

        return Command::SUCCESS;
    }
}
