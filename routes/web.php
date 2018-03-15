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
Route::get('research/competitor', 'ResearchController@competitor')->name('research.competitor');
Route::get('research/item/{id?}', 'ResearchController@item')->name('research.item');
# END RESEARCH

# REPORTS
Route::get('reports/by_days', 'ReportsController@byDays')->name('reports.by_days');
Route::get('reports/by_weeks', 'ReportsController@byWeeks')->name('reports.by_weeks');
Route::get('reports/by_months', 'ReportsController@byMonths')->name('reports.by_months');
Route::get('reports/by_years', 'ReportsController@byYears')->name('reports.by_years');
# END REPORTS

# LISTING BUILDER
Route::get('lister/jobs', 'ListerController@jobs')->name('lister.jobs');
Route::get('lister/start', 'ListerController@start')->name('lister.start');
Route::get('lister/customize', 'ListerController@customize')->name('lister.customize');
Route::get('lister/preview/{template}', 'ListerController@preview')->name('lister.preview');
Route::post('lister', 'ListerController@submit')->name('lister.submit');
# END LISTING BUILDER

# OAUTH
Route::get('oauth/google/redirect', 'OAuth\\GoogleController@redirect')->name('oauth.google.redirect');
Route::get('oauth/google/callback', 'OAuth\\GoogleController@callback')->name('oauth.google.callback');
# END OAUTH