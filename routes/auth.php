<?php

Auth::routes();

# EBAY AUTH'N'AUTH AUTHORIZATION FLOW
Route::get('ebay/auth', 'AuthnAuthController@signin')->name('ebay.auth');
Route::get('ebay/callback', 'AuthnAuthController@callback')->name('ebay.callback');
# END EBAY AUTH'N'AUTH AUTHORIZATION FLOW

# OAUTH
Route::get('oauth/google/redirect', 'OAuth\\GoogleController@redirect')->name('oauth.google.redirect');
Route::get('oauth/google/callback', 'OAuth\\GoogleController@callback')->name('oauth.google.callback');
# END OAUTH