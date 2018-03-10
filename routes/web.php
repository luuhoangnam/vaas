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
Route::get('items', 'ItemsController@index')->name('items');
Route::get('items/{item}', 'ItemsController@show')->name('items.show');
# END LISTINGS

# RESEARCH
Route::get('research/compare', 'ResearchController@compare')->name('research.compare');
# END RESEARCH

# REPORTS
Route::get('reports', 'ResearchController@compare')->name('reports');
# END REPORTS

# LISTING BUILDER
Route::get('listings/builder/start', 'ListingBuilderController@start')->name('listings.builder.start');
Route::get('listings/builder', 'ListingBuilderController@build')->name('listings.builder');
# END LISTING BUILDER