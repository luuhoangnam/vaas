<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Performance {{ $filtered ? ' on Filtered Items' : '' }}</span>
        <span class="text-muted text-right">
            EPI: <strong>E</strong>arning <strong>P</strong>er <strong>I</strong>tem
            |
            OPI: <strong>O</strong>rders <strong>P</strong>er <strong>I</strong>tem
            |
            AIV: <strong>A</strong>verage <strong>I</strong>tem <strong>V</strong>alue
            |
            STR: <strong>S</strong>ale <strong>T</strong>hrough <strong>R</strong>ate
        </span>
    </div>
    <div class="card-body">

        <div class="row">
            <div class="col">
                <div class="card-text align-content-center">
                    <h6 class="text-center text-uppercase">Earning</h6>
                    <h2 class="text-center">{{ usd($reporter->earning()) }}</h2>
                </div>
            </div>
            <div class="col">
                <div class="card-text align-content-center">
                    <h6 class="text-center text-uppercase">Items</h6>
                    <h2 class="text-center">{{ number_format($reporter->total()) }}</h2>
                </div>
            </div>
            <div class="col">
                <div class="card-text align-content-center">
                    <h6 class="text-center text-uppercase">Orders</h6>
                    <h2 class="text-center">{{ number_format($reporter->ordersCount()) }}</h2>
                </div>
            </div>
            <div class="col">
                <div class="card-text align-content-center">
                    <h6 class="text-center text-uppercase">Total Value</h6>
                    <h2 class="text-center">{{ usd($reporter->value()) }}</h2>
                </div>
            </div>
            <div class="col">
                <div class="card-text align-content-center">
                    <h6 class="text-center text-uppercase">EPI</h6>
                    <h2 class="text-center">{{ usd($reporter->averageEarningPerItem()) }}</h2>
                </div>
            </div>
            <div class="col">
                <div class="card-text align-content-center">
                    <h6 class="text-center text-uppercase">AOV</h6>
                    <h2 class="text-center">{{ usd($reporter->averageOrderValue()) }}</h2>
                </div>
            </div>
            <div class="col">
                <div class="card-text align-content-center">
                    <h6 class="text-center text-uppercase">OPI</h6>
                    <h2 class="text-center">{{ number_format($reporter->averageOrdersPerItem(), 2) }}</h2>
                </div>
            </div>
            <div class="col">
                <div class="card-text align-content-center">
                    <h6 class="text-center text-uppercase">AIV</h6>
                    <h2 class="text-center">{{ usd($reporter->averageItemValue()) }}</h2>
                </div>
            </div>
            <div class="col">
                <div class="card-text align-content-center">
                    <h6 class="text-center text-uppercase">STR</h6>
                    <h2 class="text-center">{{ percent($reporter->saleThroughRate()) }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>