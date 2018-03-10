Vue.component('dashboard-category-chart', {
    props: ['config'],

    data() {
        return {
            //
        }
    },

    mounted() {
        const chart = new Chart('category-chart', this.config);
    },

    computed: {
        //
    },

    methods: {
        //
    }
});