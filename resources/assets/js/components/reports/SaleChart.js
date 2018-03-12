Vue.component('reports-sale-chart', {
    props: ['config'],

    data() {
        return {
            //
        }
    },

    mounted() {
        const config = {
            options: {},
            ...this.config
        };

        const chart = new Chart('sale-chart', config)
    }
});