<?php

return [
    'meta' => [
        /*
         * The default configurations to be used by the meta generator.
         */
        'defaults' => [
            'title' => env('APP_NAME'), // set false to totally remove
            'titleBefore' => false, // Put defaults.title before page title, like 'It's Over 9000! - Dashboard'
            'description' => env('APP_DESCRIPTION'),
            'separator' => ' | ',
            'keywords' => [''],
            'fb:app_id' => env('FB_APP_ID'),
            'canonical' => null, // Set null for using Url::current(), set false to total remove
            'image' => env('APP_URL').'/og.png',
        ],
        /*
         * Webmaster tags are always added.
         */
        'webmaster_tags' => [
            'google' => env('GOOGLE_SITE_VERIFICATION', null),
            'bing' => env('BING_SITE_VERIFICATION'),
            'alexa' => null,
            'pinterest' => null,
            'yandex' => null,
            'norton' => null,
        ],

        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        /*
         * The default configurations to be used by the opengraph generator.
         */
        'defaults' => [
            'title' => env('APP_NAME'), // set false to total remove
            'description' => env('APP_DESCRIPTION'), // set false to total remove
            'url' => null, // Set null for using Url::current(), set false to total remove
            'type' => 'website',
            'site_name' => env('APP_NAME'),
            'image' => env('APP_URL').'/og.png',
            'images' => [
                env('APP_URL').'/og.png',
            ],
        ],
    ],
    'twitter' => [
        /*
         * The default values to be used by the twitter cards generator.
         */
        'defaults' => [
            'card' => 'summary_large_image',
            //            'site' => '@'.env('TWITTER_USERNAME'),
            'description' => env('APP_DESCRIPTION'),
            'domain' => parse_url(env('APP_URL'))['host'] ?? env('APP_URL'),
        ],
    ],
    'json-ld' => [
        /*
         * The default configurations to be used by the json-ld generator.
         */
        'defaults' => [
            'title' => env('APP_NAME'), // set false to total remove
            'description' => env('APP_DESCRIPTION'), // set false to total remove
            'url' => null, // Set null for using Url::current(), set false to total remove
            'type' => 'WebPage',
            'images' => [
                env('APP_URL').'/og.png',
                env('APP_URL').'/og.png',
            ],
            //            'sameAs' => [
            //                'https://instagram.com/'.env('FACEBOOK_USERNAME'),
            //                'https://instagram.com/'.env('TWITTER_USERNAME'),
            //                'https://instagram.com/'.env('THREADS_USERNAME'),
            //                'https://instagram.com/'.env('INSTAGRAM_USERNAME'),
            //            ],
        ],

        'publisher' => [
            '@type' => 'Organization',
            'name' => env('APP_NAME'),
            'description' => env('APP_TAGLINE'),
            'logo' => [
                '@type' => 'ImageObject',
                '@id' => env('APP_URL').'#logo',
                'width' => 256,
                'height' => 36,
                'url' => env('APP_URL').'/images/logos/full.png',
                'contentUrl' => env('APP_URL').'/images/logos/full.png',
                'caption' => env('APP_NAME').' logo',
            ],
        ],
    ],
];
