<?php

namespace App\Support\Goodreads;

use App\Models\Book;
use App\Transformers\BookTransformer;
use App\Actions\Books\FetchOrCreateBook;
use App\Actions\Books\ImportBookFromData;
use App\Contracts\BookApiServiceInterface;

class GoodreadsBookMatcher
{
    public function __construct(
        protected GoodreadsRowNormalizer $normalizer,
        protected BookApiServiceInterface $booksApi
    ) {}

    /**
     * @param  array{
     *     title: string,
     *     identifier: ?string,
     *     authors: array<int, string>,
     *     primary_author: ?string
     * }  $row
     */
    public function match(array $row): ?Book
    {
        $book = $this->findExistingByIdentifier($row['identifier'] ?? null);

        if ($book) {
            return $book->loadMissing(['authors', 'publisher', 'tags']);
        }

        $book = $this->importByIdentifier($row['identifier'] ?? null);

        if ($book) {
            return $book->loadMissing(['authors', 'publisher', 'tags']);
        }

        return $this->matchByTitleAndAuthor($row);
    }

    protected function findExistingByIdentifier(?string $identifier): ?Book
    {
        if (! $identifier) {
            return null;
        }

        return Book::query()
            ->where('identifier', $identifier)
            ->first();
    }

    protected function importByIdentifier(?string $identifier): ?Book
    {
        if (! $identifier) {
            return null;
        }

        try {
            return ImportBookFromData::run($identifier);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array{
     *     title: string,
     *     authors: array<int, string>,
     *     primary_author: ?string
     * }  $row
     */
    protected function matchByTitleAndAuthor(array $row): ?Book
    {
        $results = $this->booksApi->search(
            query: $row['title'],
            author: $row['primary_author'],
            maxResults: 5,
            page: 1,
        );

        $candidate = collect($results['items'] ?? [])
            ->map(function ($item) {
                try {
                    return BookTransformer::handle($item);
                } catch (\Throwable) {
                    return null;
                }
            })
            ->filter()
            ->first(fn (array $candidate) => $this->isCredibleMatch($row, $candidate));

        if (! $candidate || empty($candidate['identifier'])) {
            return null;
        }

        try {
            return FetchOrCreateBook::run($candidate['identifier']);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array{title: string, authors: array<int, string>}  $row
     * @param  array{title: ?string, authors?: array<int, array{name: string}>}  $candidate
     */
    protected function isCredibleMatch(array $row, array $candidate): bool
    {
        $rowTitle = $this->normalizer->normalizeTitleForComparison($row['title']);
        $candidateTitle = $this->normalizer->normalizeTitleForComparison((string) ($candidate['title'] ?? ''));

        if ($rowTitle === '' || $candidateTitle === '') {
            return false;
        }

        $titlesMatch = $rowTitle === $candidateTitle
            || str_contains($rowTitle, $candidateTitle)
            || str_contains($candidateTitle, $rowTitle);

        if (! $titlesMatch) {
            return false;
        }

        $rowSurnames = collect($this->normalizer->surnames($row['authors']));
        $candidateSurnames = collect($this->normalizer->surnames(
            collect($candidate['authors'] ?? [])
                ->pluck('name')
                ->all()
        ));

        return $rowSurnames->intersect($candidateSurnames)->isNotEmpty();
    }
}
