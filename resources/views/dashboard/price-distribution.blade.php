<price-distribution-chart :config="{{ json_encode($priceDistributionChart) }}" inline-template>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            Price Distribution

            <button @click="toggleType" type="button" class="btn btn-outline-primary btn-sm">Toggle</button>
        </div>

        <div class="card-body">
            <canvas id="price-distribution-chart" height="100"></canvas>
        </div>
    </div>
</price-distribution-chart>