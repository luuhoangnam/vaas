<dashboard-sale-chart :config="{{ json_encode($saleChart) }}" inline-template>
    <div class="card">
        <div class="card-header">Sale Chart</div>

        <div class="card-body">
            <canvas id="sale-chart" height="100"></canvas>
        </div>
    </div>
</dashboard-sale-chart>