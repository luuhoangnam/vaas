<dashboard-category-chart :config="{{ json_encode($categoryChart) }}" inline-template>
    <div class="card">
        <div class="card-header">Sale Stats by Top Categories</div>

        <div class="card-body">
            <canvas id="category-chart" height="100"></canvas>
        </div>
    </div>
</dashboard-category-chart>