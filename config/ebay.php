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

    'apps' => [
        [
            'app_id'  => 'NamLuu-APIDropi-PRD-853c9ee3a-1c748e26',
            'dev_id'  => '60294a76-d3b1-4d20-8d51-3728bc25cec8',
            'cert_id' => 'PRD-53c9ee3a95b4-d1ed-4230-83c3-e770',
            'token'   => 'AgAAAA**AQAAAA**aAAAAA**Y3O3Wg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ADkoClAZmAoQmdj6x9nY+seQ**ZzYEAA**AAMAAA**08yKHNw/QBihxrrcpMCbPCgQnpeSU4sp2ATRBh0xtym7GmAa3WZw8Os7U9W06r8xcTyJqdbRegY9w9Wk1N4NMXlb1ahDG/IBCoYBiRgeH1d+0YPVXH1PMah6+8wn2Mkv8wTkB8YK08Gwvje9lyxRF2LyVFqPlmAm+AgSQVs055oSemrFzgCoHGkH/fFcXKi/bB4nzNDDyWn84cjsFrLyfTp5gPUoUthGqDWhwvrpBF32mltoO0cgzgtm3GV7Z5h+g5PL44CPBjtyY2vBSyr5PCGpzp0SFay4ciWPFU6AceU8SZXxMsmLrBZ3/mGgbQ3kZ3FirQ/rIivqtagNSrWnrB8gqEXwEFU/h4v1wm17s24GDLwCy8bDMh8GXPhAC7I4rF3XbNY+inID8IF6EqNJfqDkbRy5tdK0mebvvn4Z3n7IrsPe1zqxPwwS7h0Yw7/t9KPM2Ao2zThXOpedBfth3gXW/ydBi243mjgDesZqWyv5mWPi7PIgMtDCQqTzVDS0pBf00cScNM4ujmZ6ZlbW33VUrwEAwJ+290UzLcc3OoZfjS8O6c/x0zfVqpV5Y1nMKaIo9rqwk5ovFn1oUaGHCx4g2AOCrAfXzmc39hDcVpAOaLFY9XQox3B/PDiwyn1I+8kOCD6rDs24Gsvbzo87UIsgLVAyjKQ8vdSE+Ow5M+4S4cGnmBphm81gybR5vZjXi6KuyRRnqL67KuhPsrwtpY97ZD5pPvS/G//y0S2zB42zp4So842Adr9FVHZYy6Hz',
        ],
        [
            'app_id'  => 'NamHoang-SellingT-PRD-6b7edec1b-863312d2', // <== Main App
            'dev_id'  => 'ae20e8cc-3170-4fbc-a4d4-d64e95454423',
            'cert_id' => 'PRD-b7edec1b6e85-983c-422b-a0e4-cae6',
            'token'   => 'AgAAAA**AQAAAA**aAAAAA**GWyOWg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ADkoClAZmAoQmdj6x9nY+seQ**Ld4DAA**AAMAAA**I3i2QxW8u/ddxIIFTHYK9Yt/jy645kfxzaE/ISPihmqW+6RUQYKAlOK6Tag63BJ7gwe7ZxgzFDtT96Gb+H78wqEq1Zzv/ZE73KVO277bryAIjvGUf3FByAssGxyUYpKLzSvgrzQ+qeQfZU2sNxdE/S62oYaYpjs7wxMRYv4ddwTpKoReIQU+fBtKWwWfbTsDTB2pi9K/tsjKO6brhED+i0INl6u3AeLIHR0tR4S8r68+u5bX+El6HUzAse2egNMolG3tSeH7ReudHkfm4Igo9H/RRuBiq3Bq32y/f6H+a571cqFr6aD2in+Bd+4paZ4LhRPajdQJrshjkKYZSp+BTlvOex/FDixp8R22dcgG+jiHW3G40G4wLObNpVf44ncirbMO520Jz+ZvBNNd7VHJ37N/6R1YYEjtjpru/8y3Z160XioCM3sPkgXVcu0/ySRarWfIf7GrJ3+EgELw6Acv1qqfA3wfse0Hid/Jhs87ZvMLFzXLjQpF1trdz+P+cchVHf2DquvEF7AtCbg/QuDgaCeveB0r0Hzr41s/A4yFV1NWx/ImWu/4MX+bc6YINzTIMvBSnXPLXI9gfgPK5W2YmAJZ01b/oKVATvvRFv6HNqNGxNHHn7+Xhq9ILqbo27mDjeYPxrkn6xZ1XkTSjqaUHcmvAQ6ZqtPmtgADTXTRWnveUrHUZ7KAQidFg/tyoy5TgWA/JGeJoR/cxeU3Mj8Uq1edlsH0GBOberbuWdsL4LhFDx0DeVYxFLoV3v7A921H',
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