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

# PROXY AUTO CONFIG
Route::get('proxy.pac', 'ProxyController@pac');
# END PROXY AUTO CONFIG

# PRODUCT RESEARCH (LIKE ZIK)
Route::get('search/seller', 'SearchController@seller')->name('search.seller');
# END PRODUCT RESEARCH (LIKE ZIK)