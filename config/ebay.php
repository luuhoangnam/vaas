<?php

use App\eBay\FindingAPI;
use App\eBay\TradingAPI;

return [
    'final_value_rate' => 9.15,
    'paypal_rate'      => 3.4,
    'giftcard_rate'    => 3.75,

    'api' => [
        'cache_time' => 1,

        TradingAPI::class => [
            'cache_time' => 5,
        ],

        FindingAPI::class => [
            'cache_time' => 60,
        ],
    ],

    'miner' => [
        'competitor' => [
            'min_feedback' => null,
            'max_feedback' => null,
            'blacklist'    => [],
        ],
    ],

    'apps' => [
        [
            'app_id'  => 'krtmebpk-krtmebpk-PRD-6e0557ea3-2ad64542',
            'dev_id'  => '7bad8c2a-6e4f-431a-bbce-115247ffd619',
            'cert_id' => 'PRD-e0557ea33a29-a4a4-43c5-8e72-2245',
            'token'   => 'AgAAAA**AQAAAA**aAAAAA**XAnbWg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ADl4SjAJGLow2dj6x9nY+seQ**BEMEAA**AAMAAA**h3WyAtfjP2+w0/In6cQK/qKNVTfWADvU7ib8/EdEFZCEehpm/ReZJxzLtkMosFBo+oiaWYYSVF8dJJyMF9qGERgfeBf8CbXOinydMcw+kXaPKIgCiV+yYsItCQZYlauGaU9Cql1Rn/rBkYFdd1F9SjhHPGO4qk/R1GxH0ae/FXbtoM7MNoQt83ePnaWEqJXDnb4vZpvWWyeK2goP4Dc2wki64EyZL4Xr8GF3erDqUT3Te4gh/JWYR36kWr7ZQdZzNwuAxHHaPOMHsJRaRApoaiouAkfhApowLMyUlKdwV23U4uw+vtHoi26vzkmClExy5LyueFRMcHy/lp6vRBlcDjIeEp4W85xHkizhxScan+vUWhA16cLrvDtUIfHUrP4mEE0Qad/J84BTs9+vluTP/NT/1UGYRtNo3zGschAxEH5UKN2Yi/qXsItUPEEBYpHnstnvh3ZUEVrYTF3oxT482fyL1H5HxKbw1iVwmlodj4Di7XJdNSTMxseaWkV5fPxpwMOdZVBSKPrfmN6Wh1xN/G0xxbuyPnIFakW/SgGe9zLpurkWssX+r/oUUyban0K84n9g9Gmb18nC3XnlnZrsFBxqocrqNl1mfIoiZ9HWZjx3bIh/bbi2N3dkYP30YJ+viHF+YVNp0HjC3BrqKN+JPczU/Zd3x7BHRJCwjForjwsgFzyOQoE/B5Y7nGGF5dhYf9wA5bSlXbRc+zKGOgSR5tP1vcWLYwaRKmzfnVCF0uOZYWZmvxS2dXmFkmjwj7aZ',
        ],
        [
            'app_id'  => 'tzjrejfn-tzjrejfn-PRD-0e043dc2e-33fb041d',
            'dev_id'  => '6e33bb0a-41ff-460b-ac6f-45f0e91c10da',
            'cert_id' => 'PRD-e043dc2e106b-0a6e-4e52-ac60-a8f3',
            'token'   => 'AgAAAA**AQAAAA**aAAAAA**3QrbWg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ADl4SjAJGLow2dj6x9nY+seQ**BUMEAA**AAMAAA**GPReI3Xn5pr1KSolT4g+Q55zhZBGzJ0iI/1Bgq+fF3/Gc4PL/oB0/gG3qfVPlclL7VerkJu6qL1fXDV9piLe8Nr5sfTtkg+Zup6ok+KiGftDXk5eHb9OBQ2zS+RGYq5Lu/17mehovQxIcNwBpZsNE/FzmzjFik/qhEA2Zm7Up/EHzXwEXRbnTWJHj7VrdO1e9yN7/U7o9m3uD06t0ZJbzO0xHE2RKuaUv6HBKDWeYEGgGnxBXYoQdAl0syG2/kgtnzDXy+gldQrQ+zLzEZln7xJDxey1d0pGgwb65PTR1i6QZVByyVxkQbKfukMjKw1l37q02A2eMVqvn40X7oUeFSNtegtmowECT7L7KFS5dIGB1j/XVQpmF5rPkw0zH/qiMrkQvqSSwXp+D/JKgbRgd3a7lv0/yCjNV1mPbAmNF3T3vcIKxVF0TsXaZyhmkDTbPTB4aeqp4MluYf5VDGAgcbCJB73/3xjnolt2ce0Y5plflJURt05Rdp7hZjtegvRzovp3b+HHruncDn6s+bwoyhlDRLE+DPLtxvYfWp5gYq8PsTJVkwrs1qe6gBSn7YTTgRdTkHre3d8Z8OsyOST+HyY01fKtyly5q09D2dhToxu2thiNm5oN9LVQ8QaGXZmXsOCGDRGHL1FUCx0RQmU9Y0ZwH4pVDD6Au7j+ovfSWmdwjjv3czfp40vWOUvDgqgWW5tXC84Ig0NVs6IScHldOxGrpoWJIltzF5JBqE/wVa8nEs4yjUEbPfk6Qq5oUE9D',
        ],
        [
            'app_id'  => 'uklcvymd-uklcvymd-PRD-7e0557ea3-4d2a6936',
            'dev_id'  => '50360fd4-c596-46ca-8c43-7cbfaaa39ee6',
            'cert_id' => 'PRD-e0557ea3a7bb-0e65-480c-90a5-f691',
            'token'   => 'AgAAAA**AQAAAA**aAAAAA**TgvbWg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ADl4SjAJGLow2dj6x9nY+seQ**BkMEAA**AAMAAA**9CaJavrFxBa3OcoPuC8OwbY7AWDD4GHuL4jmGUVE+XSeMpNlMbp1YsUalMJFCqrO1v/cp33O+S+Co3YHgqcDwlSIUOkCN46BCmQlHDoJUa4C1YFGSDJMkeuJ3IAaHb6SWCgmfKWdDT0hG4zAMlBvKD6+mTzueyf9hfcTEs6SiqMhQfN5DDhe/Y4jYTS62Izs3xU9nxfWbIVjRlc1vT5qBq/fp6u+qZYVLr+wZKr31iUIFl6VQtJMHrYYTARslEmEILU+hDMTmWyYXrKRoBV27zrrdJjCRFSWNqaZol+T0I8hI/mtXEzrcrhKGF4n/nFPUXl2H5IiydOTY/NOPE/wkw/IIMVd438IrNkBeujj9hgGHttXTLhNJcVGDABwSfLN3kKBuZJrizFI+GXV6OAIsjfZTIP5SH++AdwJvGkwgJWpJsORlhZbI5zPZS7JbHRqsBk82GFeESXgR/vH4VuJIB1y4jY7Xoc09nFyVRT8hO1nnYx5Yo7EQvKk2Pw0LgZ9uxLxweKL7VOHNRPMmQSoMi/HrXyl7YHOLhphWSVDFZjECLB+kpMY/5UIGrNFOYLHaP3mMTn5SQsZzINrPKGcbt8F1ssAEnzrS2ijSpJDWwc4EDvsvzkrWNjMrqAjz5M9o03FNtFpnGiVEyXKRCOtIhoO4ZqUh4/JtV52H8UZpOSMEpvzFjqXfIs6EUXzd0eiN75oG02jTg1VyTGX9hFde8zjxSC0Z5evsMOVnwYmpQJD0oxDFGzLEArbiqmApJ2e',
        ]
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
            'performance' => [
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
            'sourcing'    => [

            ],
        ],
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