<?php

use Illuminate\Http\Request;


Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    # EBAY INTERACTIONS
    Route::group(['prefix' => 'accounts/{account}'], function () {
        Route::post('add_item', 'Account\ItemsController@addItem');

        // Supports
        Route::post('suggest_category', 'Account\ItemsController@suggestCategory');
        Route::post('seller_profiles', 'Account\ItemsController@sellerProfiles');
        Route::post(
            'category_supported_conditions/{category_id}',
            'Account\ItemsController@categorySupportedConditions'
        );
    });
# END EBAY INTERACTIONS
});