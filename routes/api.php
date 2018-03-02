<?php

use Illuminate\Http\Request;


Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    # ACCOUNT
    Route::get('/user/accounts', 'AccountsController@myAccounts');
    # END ACCOUNT

    # EBAY INTERACTIONS
    Route::group(['prefix' => 'accounts/{account}'], function () {
        Route::post('add_item', 'Account\ItemsController@addItem');

        // Supports
        Route::post('suggest_category', 'Account\ItemsController@suggestCategory');
        Route::post('seller_profiles', 'Account\ItemsController@sellerProfiles');
        Route::post('allowed_conditions/{category_id}', 'Account\ItemsController@allowedConditions');
    });
# END EBAY INTERACTIONS
});