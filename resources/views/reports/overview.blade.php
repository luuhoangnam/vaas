<div class="card">
    <div class="card-header">Overview</div>

    <div class="card-body">
        <div class="row">

            @include('snippets.statistic', ['title' => 'Revenue', 'number' => usd($reporter->revenue())])

            @include('snippets.statistic', ['title' => 'Orders', 'number' => number_format($reporter->count())])

            @include('snippets.statistic', ['title' => 'Profit', 'number' => usd($reporter->profit())])

            @include('snippets.statistic', ['title' => 'Margin', 'number' => percent($reporter->margin())])

            @include('snippets.statistic', ['title' => 'Cost Of Goods', 'number' => usd($reporter->costOfGoods())])

            @include('snippets.statistic', ['title' => 'Fees', 'number' => usd($reporter->fees())])

            @include('snippets.statistic', ['title' => 'Cashback', 'number' => usd($reporter->cashback())])

            @include('snippets.statistic', ['title' => 'AOV', 'number' => usd($reporter->averageOrderValue())])

        </div>
    </div>
</div>