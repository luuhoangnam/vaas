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

    'miner' => [
        'competitor' => [
            'min_feedback' => null,
            'max_feedback' => null,
            'blacklist'    => [],
        ],
    ],

    'apps' => [
        [
            'app_id'  => 'wkrvpqew-wkrvpqew-PRD-653e361d2-a44bce4e',
            'dev_id'  => 'e46e76a5-d3de-4041-98ca-ad68b4412e9f',
            'cert_id' => 'PRD-53e361d2729b-d073-4375-8b6e-2923',
            'token'   => 'AgAAAA**AQAAAA**aAAAAA**Tl+4Wg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ADkoClAZmAoQmdj6x9nY+seQ**kDYEAA**AAMAAA**ghw3QYW/DTAx/PBs9JSyhn+rLpMhAD0kxdOXZaWOS0nqK4njKkGfiqxZzb0gyQ0UmCjeii4e2svhlLSRysPKjr7ouBGyyfCL9Fty0ppgwV+xR5esP7T2ki7rtEH8W1XvmuAEHsWtKVGLR6rUv8D2RYW5QJV4RWXysdAjcG7hMDl6M2VwjQbZVnmLiwu0tp3T2CrYy7wPw2TxU9Vb4jYWmovlWH8QhI4WaPLtLYCBQO0nMtJA3efw91w3VFfO0V5GqmPafy/oQqWK3h0YVXdIxlfACWFZwagcX7DB0zsaUw3eno1euuVvVzNACeOa9hzkGxv+1JRqIKPaU0Fn4x8CwKLjTWK4i3WG/BCfp7v0VXsDnHNm4O0qXY1b5DsYTAZ8G4AkJwfdN93uqXdeJ+owIIZlJRR063qSGEO5TS9cUssrziFtC/TwRzyaEuuxbzjZOKR3lIaXZQXThIr821yjxHY5PjxwDO5VwbngRL28M22bROrADRKrdmFz/0FI3o8bjUwOn/Sb5J+gpli+TnJSOwQ1KQz3hDLtkDjKQTVItgpWs/xvh5jWpDSwxFQ4YUK20kBWm6HkNLepnP1rqxIpyTf77SYTce/FUumaHtiNGUPIMTdS6fZrKvUoQtyH1+euVJFarutWztR/4se0gmvXxwXrc1+3l124oT2Zpj5c19sHaw9gMfLGzF4Myuaef/umzkIETy6I7V0U90yDIe9RtdBkaYkR5n+hjUmb/P0qXS4Azv6FfwAZg7osf4e3Bxwz',
        ],
        [
            'app_id'  => 'eekbfxyh-eekbfxyh-PRD-d53da1965-adcc7120',
            'dev_id'  => '1b740dd3-e039-4f21-a27b-4a3050531d31',
            'cert_id' => 'PRD-53da1965c219-bd75-453e-8511-0862',
            'token'   => 'AgAAAA**AQAAAA**aAAAAA**dUm4Wg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ADkoClAZmAoQmdj6x9nY+seQ**izYEAA**AAMAAA**FS8iOGbSGCdmxNyzTHYmqVyhFDCXM2yhzJ/KBdK6G/7cGmRGfr1fgKPCMEMaB77sU3pbCCTlLlasDEKthjNnvkAjzwE0uIVOIUdrm3qBFKM0aP3b0b53wCxEYO/JB0ygLZh3phGyY9QnkwdgxX8zTzOee0EuVNizs3jimkwvgdbxdnac+5NSoGyKp1goWEYVuaISRWNPMONAdFQAS8p3ztzpk5Rd9vbdtDvOFvPCt5fmaiVbegQz55FgGrjAnn6wDpFco0WtiyQHWYzbpFuKl1vgLOYINkPHpXnOlR7yBUsM+XYbj2JJ4LvEyeOuXKmCku2d2zz6Oc0CAlr+z0KiUsukMK89Ng/m7iQTOqNiqn54LJPdryBx3L65fIyq7qbfJTAawbvE7tbqlT1fByuK3tIidei7lN0OMq1IouN4aXX1O9UlKJsdxAcsk5jkyqMVxTfjE15Zs9QKNqFkc44fhU3dD01GwNuUDh2Fe5oO4SkmKaXnEQRc+aR8TccMYrPCXvD8etJOnVVn56G1XP+b6h+avYBaSCVQFA+y+IRwfWOS0W1fZZHiVruxQsqk22kCjKS7SBUfsEDSCgLbet4+wVo5sxVy003AcQhNPviUPaJmk/YH+boxBcGZfhyXXnX08nc+5aDblc0mEWZVkPPTat3bw1TfrW2XiTdhjbKyvjExzAXWJK32BQ4k2Q1Z58i0FQ9W3RRNlITxB+duQTg7dwU3X7Tl7b0jVEyGVTvC7mIuvl27tHiNr8+A1D1WzDwj',
        ],
        [
            'app_id'  => 'NamLuu-APIDropi-PRD-853c9ee3a-1c748e26',
            'dev_id'  => '60294a76-d3b1-4d20-8d51-3728bc25cec8',
            'cert_id' => 'PRD-53c9ee3a95b4-d1ed-4230-83c3-e770',
            'token'   => 'AgAAAA**AQAAAA**aAAAAA**Y3O3Wg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ADkoClAZmAoQmdj6x9nY+seQ**ZzYEAA**AAMAAA**08yKHNw/QBihxrrcpMCbPCgQnpeSU4sp2ATRBh0xtym7GmAa3WZw8Os7U9W06r8xcTyJqdbRegY9w9Wk1N4NMXlb1ahDG/IBCoYBiRgeH1d+0YPVXH1PMah6+8wn2Mkv8wTkB8YK08Gwvje9lyxRF2LyVFqPlmAm+AgSQVs055oSemrFzgCoHGkH/fFcXKi/bB4nzNDDyWn84cjsFrLyfTp5gPUoUthGqDWhwvrpBF32mltoO0cgzgtm3GV7Z5h+g5PL44CPBjtyY2vBSyr5PCGpzp0SFay4ciWPFU6AceU8SZXxMsmLrBZ3/mGgbQ3kZ3FirQ/rIivqtagNSrWnrB8gqEXwEFU/h4v1wm17s24GDLwCy8bDMh8GXPhAC7I4rF3XbNY+inID8IF6EqNJfqDkbRy5tdK0mebvvn4Z3n7IrsPe1zqxPwwS7h0Yw7/t9KPM2Ao2zThXOpedBfth3gXW/ydBi243mjgDesZqWyv5mWPi7PIgMtDCQqTzVDS0pBf00cScNM4ujmZ6ZlbW33VUrwEAwJ+290UzLcc3OoZfjS8O6c/x0zfVqpV5Y1nMKaIo9rqwk5ovFn1oUaGHCx4g2AOCrAfXzmc39hDcVpAOaLFY9XQox3B/PDiwyn1I+8kOCD6rDs24Gsvbzo87UIsgLVAyjKQ8vdSE+Ow5M+4S4cGnmBphm81gybR5vZjXi6KuyRRnqL67KuhPsrwtpY97ZD5pPvS/G//y0S2zB42zp4So842Adr9FVHZYy6Hz',
        ],
        [
            'app_id'  => 'NamDavid-beleclzf-PRD-478771aae-fd123dab',
            'dev_id'  => 'a6fe4979-aa39-45dd-92be-ecb8b69a77b2',
            'cert_id' => 'PRD-78771aae0aab-5dce-4e35-b486-74cd',
            'token'   => 'AgAAAA**AQAAAA**aAAAAA**YznDWg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ADkoClAZmAoQmdj6x9nY+seQ**tjoEAA**AAMAAA**RIpfR1A/S7xbLikb/c35FNFCbsymkmsQt1e2hB9LO37oRe7y/cdE7RVe4tiGAZw1Sui4GvPnOsp1/f/G3/hWh6/iDNvhTDRdGCk9NhN46ABjQp41dqFzVRNethm2fD/8vEiI33j8guYk1FUkahox6OXJKA3Gvqfgx4tt5GTej8TTz9ZIpfj/pCEMNWo7gmgsBAs+Wk/OLIi471X0Yk0fOegwiYu0NWGZC7PjNoIR9Sn3x6uQu4q8PW5BmAOG1weF1PfEDv5SR5mYy0AvevX9cPDpyp8/D8BZj43LC3dP+0fnY7lhcAf+/O5E4bUywdsv3/CWHNa8P5hbIxq0FEJDKiGvsy5Rfn3K2VHyY8Gs3IbAV492yKkVpumttZisoI1eHqakBj+80rmycMf/+tBImid5RPw4I1tR+dnnetBDbDea6iYfE6NeXlxW14p10qOwxgFAe/yEm47GRQXuhHBB0JeszMDMlxYRhVdejVWxfOrDGbuIrbr0sH+QJAgajFPUZGfM1ChhfkdI/zs3PhYC3Aut1qBnOG7lk2e3kgh4ETSE94vgKqDmR+Nu9ElqSmD5ALpU4SxSVighjCoN4qzW19nMwWKNKsqTNJBC1xxwzcevYYJ1sETN5lX16GE5O0xjeO2LERP2TGkXCifJVzvvgh1pipOMD0ojtlH3cZiJlm2/IL0iBjNIJCz3yn7V6rNvLYnLnLKYQAksge2491f3Rr2wpZGs30cFIrO4iAe2nrr8q3rjHEd+AArKuPzFxx6s',
        ],
        [
            'app_id'  => 'mtrbachw-mtrbachw-PRD-078731904-4d3265fb',
            'dev_id'  => 'b65db81b-97f7-42af-af6f-232efbd5bfd3',
            'cert_id' => 'PRD-7873190414fa-3b60-4a99-9046-97ef',
            'token'   => 'AgAAAA**AQAAAA**aAAAAA**0DrDWg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ADkoClAZmAoQmdj6x9nY+seQ**uDoEAA**AAMAAA**uJV7mOnsuGm5x8lefuQUi29xqxzTchOdlyIGcxA4eMm2GYteTYf807ZuD2OKp9lGkozFXLyVBOCUYJ2xb8vV8nNanJTHXU9L0S1zJZGzFLQYrh+f5WVTLBKb8lkGNUdUiqM/wRYsf4P/MhPLqjQ8aYrcjEYaK8LzMZbi5kuf8GuCNumLPkma0YydIxdXxPvfIXOdyZtZrP5uZnw4TKUYi2E7nq+20OH1pPFe1rh8O55HpEGPWvJmxSX3vNCgRXuHmQhmikCwgNmHvh9uEHK5lzS7BpyEZQA4Wx5+kbBQzWnkSQzUGBKDMJo+/BvpfIge0F4ke6OopWrz/o96BK6YSTX00rgWxhsltKFTJSfOb60/qwxlZOUWyqBrBC4LcyEvxnrXwCloJUdmpfbU7QP3Xy9YnhxrSN7+VfzOtxNbxggzHwKcLLCxHE5EkKhHkhfT7PAmbMx8WR3eAltRatENiLO+Oq+CDiTMhoqffyz+3pV4bJYEc30gufWpD1hzqZ6gfhsHGD4xagTmEaILZEYKQVy+2XAFw+firZPMrBVzo07k6KfGQXXQYiuXMw1ze7rgm0/z/ndGFtE78+ZKlDIRn23fJXohx81XOjh5YNGJUdlJu3A9Ictu9ZQ466x4ubxboVLcoQnTUaKls23uRtvhjaopThk7WO/z/G1amKW92yk2+1OVd027MEJhkQYZp2jTvJViwOGkcwDii3aoLhykSmwWpkxCTkUWjU2UGeGIlgLyHeHHJzWgZhSi0937Y1MB',
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