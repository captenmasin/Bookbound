<?php

use App\Services\ISBNdbService;
use App\Services\GoogleBooksService;
use App\Services\OpenLibraryService;

return [
    'provider' => env('BOOKS_API', 'isbndb'),

    // map short names to concrete classes
    'providers' => [
        'isbndb' => ISBNdbService::class,
        'google' => GoogleBooksService::class,
        'openlibrary' => OpenLibraryService::class,
    ],

    'public_page_cache' => [
        'ttl_seconds' => (int) env('BOOK_PUBLIC_PAGE_CACHE_TTL', 21600),
        'version' => 'v1',
    ],
];
