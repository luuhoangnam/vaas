Vue.component('item-chart', {
    props: ['config'],

    data() {
        return {
            //
        }
    },

    mounted() {
        const chart = new Chart('item-chart', this.config)
    }
});