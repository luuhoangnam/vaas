Vue.component('dashboard', {
    props: ['data'],

    data() {
        return {
            //
        }
    },

    mounted() {
        const chart = new Chart('sale-chart', this.data)
    }
});