<?php

return [
    'research' => [
        'price_rate' => 1.3,
        'feedback'   => [
            'min' => 1000,
            'max' => null,
        ],
    ],

    'search' => [
        // Limit to only 100 pages in search result will be fetched. That's mean 10,000 items will be fetched.
        'page_limit' => 100,
    ],

    'criterias' => [
        'min_price' => 10,
    ],
];