<?php

return [
    'webhooks' => [
        'https://bc232b84.ngrok.io/ebay/events',
        'https://api.dropist.io/ebay/events',
    ],

    'lister' => [
        'tax_rate' => 9 / 100,
    ],

    'reporting' => [
        'giftcard' => 1.0275,
    ],

    'ranking' => [
        //
    ],

    'quantityManager' => [
        'ignore'             => [
            'goodie.depot',
        ],
        'autoRefillQuantity' => 1,
    ],

    'repricer' => [
        'tax_rate' => 9 / 100,
        'default'  => [
            'margin'           => 5 / 100, // 5%
            'tax'              => true,
            'final_value_rate' => 9.15 / 100, // 9.15%
            'paypal_rate'      => 3.9 / 100, // 3.9%
            'paypal_usd'       => 0.3, // $0.3
            'minimum_price'    => 0.0,
        ],
    ],

    'sourcing' => [
        'amazon' => [
            'treatNonPrimeAsNotAvailable' => true,

            'strategies' => [
//                MarketingApiFetchingStrategy::class,
                // BasicCrawlFetchingStrategy::class,
            ],
        ],
    ],

    'call_forwarding' => [
        'trading' => [
            'getSuggestedCategories' => [
                'cache_time' => 60 * 24 * 30, // 30 days
            ],
            'getUserPreferences',
        ],
    ],
];