Vue.component('sale-chart', {
    props: ['config'],

    data() {
        return {
            //
        }
    },

    mounted() {
        const chart = new Chart('sale-chart', this.config)
    }
});