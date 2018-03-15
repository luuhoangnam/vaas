const Mustache = require('mustache');

Vue.component('listing-builder', {
    props: ['template', 'product'],

    data() {
        return {
            //
        }
    },

    mounted() {
        //
    },

    computed: {
        preview() {
            const data = {
                title: this.product.title
            };

            return Mustache.render(this.template, data);
        }
    },

    methods: {
        //
    }
});