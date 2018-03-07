<?php

Route::get('/', 'PagesController@welcome');

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

# RESEARCH
Route::get('research/compare', 'ResearchController@compare')->name('research.compare');
# END RESEARCH

# LISTING BUILDER
Route::get('listings/builder/start', 'ListingBuilderController@start')->name('listings.builder.start');
Route::get('listings/builder', 'ListingBuilderController@build')->name('listings.builder');
# END LISTING BUILDER