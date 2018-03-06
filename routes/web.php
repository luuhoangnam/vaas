<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

# EBAY AUTH'N'AUTH AUTHORIZATION FLOW
Route::get('ebay/auth', 'AuthnAuthController@signin')->name('ebay.auth');
Route::get('ebay/callback', 'AuthnAuthController@callback')->name('ebay.callback');
# END EBAY AUTH'N'AUTH AUTHORIZATION FLOW

# ORDERS
Route::get('orders', 'OrdersController@index')->name('orders');
# END ORDERS

# INTERMEDIATE
Route::get('redirect/amazon/{asin}', 'RedirectController@redirect')->name('redirect.amazon');
# END INTERMEDIATE

# LISTINGS
Route::get('listings', 'ItemsController@index')->name('items');
# END LISTINGS