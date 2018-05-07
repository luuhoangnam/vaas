<div class="card">
    <div class="card-header">Performance Overview</div>

    <div class="card-body">
        <div class="row">

            @include('snippets.statistic', ['title' => 'Revenue', 'number' => usd($revenue), 'change' => $revenueChange])

            @include('snippets.statistic', ['title' => 'Orders', 'number' => number_format($ordersCount), 'change' => $ordersCountChange])

            @include('snippets.statistic', ['title' => 'Profit', 'number' => usd($profit), 'change' => $profitChange])

            @include('snippets.statistic', ['title' => 'Margin', 'number' => percent($margin), 'change' => $marginChange])

        </div>

        <div class="row">

            @include('snippets.statistic', ['title' => 'Cost Of Goods', 'number' => usd($cog)])

            @include('snippets.statistic', ['title' => 'Fees', 'number' => usd($fees)])

            @include('snippets.statistic', ['title' => 'Cashback', 'number' => usd($cashback), 'change' => $cashbackChange])

            @include('snippets.statistic', ['title' => 'Avg Order Value', 'number' => usd($aov), 'change' => $aovChange])

        </div>

        <div class="row">

            @include('snippets.statistic', ['title' => 'Avg Order Frofit', 'number' => usd($aof), 'change' => $aofChange])

            @include('snippets.statistic', ['title' => 'Cashback Rate', 'number' => percent($cashbackRate), 'change' => $cashbackRateChange])

            @include('snippets.statistic', ['title' => 'Sell Through', 'number' => percent($sellThrough), 'change' => $sellThroughChange])

        </div>
    </div>
</div>