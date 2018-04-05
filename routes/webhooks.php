<?php

# EBAY NOTIFICATION API
Route::any('ebay/events', "eBay\\NotificationsController@handle")->name('ebay.events');
# END EBAY NOTIFICATION API

