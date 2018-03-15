require('trumbowyg');
const _ = require('lodash');

Vue.component('lister', {
    props: ['product', 'categories', 'profiles'],

    data() {
        return {
            title: this.product.title.substring(0, 80),
            primary_category_id: _.first(this.categories).id,
            quantity: 1,
            payment_profile_id: _.first(this.profiles['PAYMENT'])['ProfileID'],
            shipping_profile_id: _.first(this.profiles['SHIPPING'])['ProfileID'],
            returns_profile_id: _.first(this.profiles['RETURN_POLICY'])['ProfileID'],
            tax_applied: true,
            final_value_rate: 9.15,
            paypal_rate: 3.9,
            profit_rate: 5
        }
    },

    mounted() {
        $('#trumbowyg-editor').trumbowyg({
            svgPath: '/vendor/trumbowyg/icons.svg'
        });
    },

    computed: {
        titleLength() {
            return this.title.length;
        },
        characterLeft() {
            return 80 - this.titleLength;
        },
        calculatedPrice() {
            const costOfGoods = this.tax_applied ? this.product.price * 1.09 : this.product.price;

            const sellingPrice = (costOfGoods + 0.3) / (100 - this.paypal_rate - this.final_value_rate - this.profit_rate) * 100;

            return Math.round(sellingPrice * 100) / 100;
        }
    },

    methods: {
        //
    }
});