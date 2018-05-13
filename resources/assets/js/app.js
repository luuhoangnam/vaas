/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Vue.component('example-component', require('./components/ExampleComponent.vue'));

require('./components/ItemsTable');
require('./components/item/ItemChart');

// Dashboard
require('./components/dashboard/OrdersTable');
require('./components/dashboard/SaleChart');
require('./components/dashboard/CategoryChart');
require('./components/dashboard/PriceDistributionChart');

// Reports
require('./components/reports/SaleChart');

// Listing Builder
require('./components/listings/Builder');

// Lister
require('./components/Lister');

require('./components/SearchSeller');

const app = new Vue({
    el: '#app'
});
