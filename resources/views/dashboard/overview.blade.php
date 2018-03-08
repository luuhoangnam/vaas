<div class="card">
    <div class="card-header">Performance Overview</div>

    <div class="card-body">
        <div class="row">

            @include('snippets.statistic', ['title' => 'Revenue', 'number' => usd($revenue), 'change' => $revenueChange])

            @include('snippets.statistic', ['title' => 'Fees', 'number' => usd($fees), 'change' => $feesChange, 'positive' => $feesChange < 0])

            @include('snippets.statistic', ['title' => 'Cashback', 'number' => usd($cashback), 'change' => $cashbackChange])

            @include('snippets.statistic', ['title' => 'Profit', 'number' => usd($profit), 'change' => $profitChange])

            @include('snippets.statistic', ['title' => 'Margin', 'number' => percent($margin), 'change' => $marginChange])

        </div>
    </div>
</div>