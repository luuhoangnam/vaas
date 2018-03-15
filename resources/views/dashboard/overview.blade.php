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

            @include('snippets.statistic', ['title' => 'Cost Of Goods', 'number' => usd($cog), 'change' => $cogChange, 'positive' => $cogChange < 0])

            @include('snippets.statistic', ['title' => 'Fees', 'number' => usd($fees), 'change' => $feesChange, 'positive' => $feesChange < 0])

            @include('snippets.statistic', ['title' => 'Cashback', 'number' => usd($cashback), 'change' => $cashbackChange])

            @include('snippets.statistic', ['title' => 'AOV', 'number' => usd($aov), 'change' => $aovChange])

        </div>
    </div>
</div>