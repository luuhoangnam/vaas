const ClipboardJS = require('clipboard');

Vue.component('items-table', {
    props: ['items'],

    data() {
        return {
            //
        }
    },

    mounted() {
        //
    },

    computed: {
        ids() {
            return this.items.map(item => item['item_id'])
        }
    },

    methods: {
        copyIds(e) {
            e.preventDefault();

            new ClipboardJS('#copyIds');

            console.log('IDs Copied!');
        }
    }
});