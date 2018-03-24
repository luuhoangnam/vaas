<?php

use App\eBay\FindingAPI;
use App\eBay\TradingAPI;

return [
    'api' => [
        'cache_time' => 1,

        TradingAPI::class => [
            'cache_time' => 5,
        ],

        FindingAPI::class => [
            'cache_time' => 60,
        ],
    ],

    'webhooks' => [
        'https://bc232b84.ngrok.io/ebay/events',
        'https://api.dropist.io/ebay/events',
    ],

    'lister' => [
        'tax_rate' => 9 / 100,
    ],

    'spying' => [
        'auto_research' => [
            [
                'field'    => 'price',
                'operator' => '>=',
                'value'    => 15,
            ],
            [
                'field'    => 'start_time',
                'operator' => '>=',
                'value'    => '3 months ago',
            ],
        ],
    ],

    'reporting' => [
        'giftcard' => 1.0275,
    ],

    'ranking' => [
        //
    ],

    'quantity_manager' => [
        'ignore'          => [
//            'goodie.depot',
        ],
        'refill_quantity' => 1,
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