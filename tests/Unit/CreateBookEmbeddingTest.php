<?php

use App\Models\Tag;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use App\Actions\Books\CreateAuthorEmbedding;

test('handles a single book instance', function () {
    Stringable::macro('toEmbeddings', fn (): array => [0.1, 0.2, 0.3]);

    $book = Mockery::mock(Book::class)->makePartial();
    $book->forceFill([
        'title' => 'The Grand Grimoire The Red Dragon',
        'description' => 'A long occult description.',
    ]);
    $book->setRelation('authors', new Collection([
        new Author(['name' => 'Anonymous']),
    ]));
    $book->setRelation('tags', new Collection([
        new Tag(['name' => 'Occult']),
    ]));
    $book->setRelation('publisher', new Publisher(['name' => 'Arcane Press']));

    $book->shouldReceive('updateQuietly')
        ->once()
        ->with(['embedding' => [0.1, 0.2, 0.3]]);

    app(CreateAuthorEmbedding::class)->handle($book);
});
