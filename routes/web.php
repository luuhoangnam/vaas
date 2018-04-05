<?php

Route::get('/', 'PagesController@welcome');
Route::get('/home', 'HomeController@index')->name('home');

# ORDERS
Route::get('orders', 'OrdersController@index')->name('orders');
# END ORDERS

# INTERMEDIATE
Route::get('redirect/amazon/{asin}', 'RedirectController@redirect')->name('redirect.amazon');
# END INTERMEDIATE

# LISTINGS
Route::get('items', 'ItemsController@index')->name('items');
# END LISTINGS