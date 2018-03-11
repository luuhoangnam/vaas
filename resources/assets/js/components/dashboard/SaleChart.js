Vue.component('dashboard-sale-chart', {
    props: ['config'],

    data() {
        return {
            //
        }
    },

    mounted() {
        const config = {
            options: {
                scales: {
                    yAxes: [
                        {
                            ticks: {
                                // Include a dollar sign in the ticks
                                callback: function (value, index, values) {
                                    return '$' + value;
                                }
                            }
                        }
                    ]
                }
            },
            ...this.config
        };

        console.log(config);

        const chart = new Chart('sale-chart', config)
    }
});