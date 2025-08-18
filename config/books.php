<?php

return [
    'provider' => env('BOOKS_API', 'isbndb'),

    // map short names to concrete classes
    'providers' => [
        'isbndb' => \App\Services\ISBNdbService::class,
        'google' => \App\Services\GoogleBooksService::class,
        'openlibrary' => \App\Services\OpenLibraryService::class,
    ],
];
