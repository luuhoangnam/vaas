Vue.component('price-distribution-chart', {
    props: ['config'],

    data() {
        return {
            type: 'bar',
            chart: null
        }
    },

    mounted() {
        this.renderChart()
    },

    computed: {
        //
    },

    methods: {
        toggleType() {
            this.type = this.type === 'bar' ? this.type = 'pie' : 'bar';

            this.renderChart();
        },
        renderChart() {
            this.chart instanceof Chart && this.chart.destroy();

            const config = {
                ...this.config,
                type: this.type
            };

            this.chart = new Chart('price-distribution-chart', config);
        },
    }
});