<?php

# EBAY NOTIFICATION API
Route::any('ebay/events', 'NotificationsController@handle')->name('ebay.events');
# END EBAY NOTIFICATION API

