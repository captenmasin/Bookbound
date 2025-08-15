<?php

return [
    'plans' => [

        'free' => [
            'name' => 'Free',
            'key' => env('FREE_PLAN_KEY', 'free'),
            'price' => 0.00,
            'features' => [
                'Up to 50 Books',
                'Scan Barcodes',
                'Search and Filter your Library',
                'Preview Book Details',
                'Track Book Status',
                'Review and Rate Books',
            ],
            'limits' => [
                'max_books' => 50,
                'private_notes' => false,
                'custom_covers' => false,
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'key' => env('PRO_PLAN_KEY', 'pro'),
            'price' => 10.00,
            'features' => [
                'Unlimited books',
                'Scan Barcodes',
                'Search and Filter your Library',
                'Preview Book Details',
                'Track Book Status',
                'Review and Rate Books',
                'Private Notes',
                'Custom Book Covers',
            ],
            'limits' => [
                'max_books' => null, // Unlimited
                'private_notes' => true,
                'custom_covers' => true,
            ],
        ],
    ],
];
