<?php

return [
    'plans' => [
        'pro' => [
            'name' => 'Pro',
            'key' => env('PRO_PLAN_KEY', 'pro'),
            'price' => 10.00,
            'features' => [
                'Unlimited books',
                'Private notes',
            ],
            'limits' => [
                'max_books' => 10,
                'private_notes' => false,
            ],
        ],
    ],
];
