<?php

return [
    'plans' => [
        'pro' => [
            'name' => 'Pro',
            'key' => env('PRO_PLAN_KEY', 'pro'),
            'price' => 10.00,
            'features' => [

            ],
            'limits' => [
                'max_books' => 10,
                'notes' => false,
            ],
        ],
    ],
];
