<?php

use App\Cashback\AmazonAssociates;
use App\Cashback\AmazonAssociatesCashbackRateResolver;
use App\Cashback\BeFrugal;
use App\Cashback\TopCashback;

return [
    'default' => 'associates',

    'cache_time' => 24 * 60, // 1 Day

    'programs' => [
        'associates' => [
            'name'           => 'Amazon Associates Program',
            'link_generator' => AmazonAssociates::class,
            'rate'           => AmazonAssociatesCashbackRateResolver::class,
        ],

        'befrugal' => [
            'name'           => 'BeFrugal',
            'link_generator' => BeFrugal::class,
            'rate'           => [
                ['id' => 2102313011, 'name' => 'Amazon Devices', 'rate' => 0.07],
                ['id' => 1055398, 'name' => 'Home and Garden', 'rate' => 0.03],
                ['id' => 16310101, 'name' => 'Grocery', 'rate' => 0.025],
                ['id' => 7301146011, 'name' => 'Prime Pantry', 'rate' => 0.03],
                ['id' => 11260432011, 'name' => 'Handmade', 'rate' => 0.025],
                ['id' => 7175545011, 'name' => 'Luxury Beauty & Grooming', 'rate' => 0.03],
                ['id' => 9479199011, 'name' => 'Luggage', 'rate' => 0.03],
                ['id' => 7589478011, 'name' => 'Amazon Video', 'rate' => 0.03],
                ['id' => 8098158011, 'name' => 'Amazon Home Services', 'rate' => 0.03],
                ['id' => 16334314011, 'name' => 'Prime Exclusive Apparel', 'rate' => 0.03],
                ['id' => 7147440011, 'name' => 'Women\'s Fashion', 'rate' => 0.03],
                ['id' => 7147441011, 'name' => 'Men\'s Fashion', 'rate' => 0.03],
                ['id' => 7147442011, 'name' => 'Girls\' Fashion', 'rate' => 0.03],
                ['id' => 7147443011, 'name' => 'Boys\' Fashion', 'rate' => 0.03],
            ],
        ],

        'topcashback' => [
            'name'           => 'Top Cashback',
            'link_generator' => TopCashback::class,
            'rate'           => [
                ['id' => 2102313011, 'name' => 'Amazon Devices', 'rate' => 0.06],
                ['id' => 1055398, 'name' => 'Home and Garden', 'rate' => 0.06],
                ['id' => 16310101, 'name' => 'Grocery', 'rate' => 0.05],
                ['id' => 7301146011, 'name' => 'Prime Pantry', 'rate' => 0.06],
                ['id' => 7175545011, 'name' => 'Luxury Beauty & Grooming', 'rate' => 0.06],
                ['id' => 9479199011, 'name' => 'Luggage', 'rate' => 0.06],
                ['id' => 11260432011, 'name' => 'Handmade', 'rate' => 0.05],
                ['id' => 7589478011, 'name' => 'Amazon Video', 'rate' => 0.06],
                ['id' => 8098158011, 'name' => 'Home Services', 'rate' => 0.06],
                ['id' => 16334314011, 'name' => 'Prime Exclusive Apparel', 'rate' => 0.06],
                ['id' => 7147440011, 'name' => 'Women\'s Fashion', 'rate' => 0.06],
                ['id' => 7147441011, 'name' => 'Men\'s Fashion', 'rate' => 0.06],
                ['id' => 7147442011, 'name' => 'Girls\' Fashion', 'rate' => 0.06],
                ['id' => 7147443011, 'name' => 'Boys\' Fashion', 'rate' => 0.06],
            ],
        ],
    ],
];