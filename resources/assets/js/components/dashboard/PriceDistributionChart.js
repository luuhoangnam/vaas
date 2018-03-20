Vue.component('price-distribution-chart', {
    props: ['config'],

    data() {
        return {
            type: 'bar',
            legend: {
                display: false,
                position: 'top'
            },
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
            if (this.type === 'bar') {
                this.type = 'pie';
                this.legend = {
                    display: true,
                    position: 'right'
                };
            } else {
                this.type = 'bar';
                this.legend = {
                    display: false
                };
            }

            this.renderChart();
        },
        renderChart() {
            this.chart instanceof Chart && this.chart.destroy();

            const config = {
                ...this.config,
                type: this.type,
                options: {
                    legend: {
                        ...this.legend
                    }
                },
            };

            this.chart = new Chart('price-distribution-chart', config);
        },
    }
});