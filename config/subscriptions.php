<?php

return [
    'plans' => [

        'free' => [
            'name' => 'Free',
            'key' => env('FREE_PLAN_KEY', 'free'),
            'price' => 0.00,
            'features' => [
                'Up to 10 books',
            ],
            'limits' => [
                'max_books' => 5,
                'private_notes' => false,
                'custom_covers' => true,
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'key' => env('PRO_PLAN_KEY', 'pro'),
            'price' => 10.00,
            'features' => [
                'Unlimited books',
                'Private notes',
            ],
            'limits' => [
                'max_books' => null, // Unlimited
                'private_notes' => true,
                'custom_covers' => true,
            ],
        ],
    ],
];
