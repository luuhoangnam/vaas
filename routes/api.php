<?php

use Illuminate\Http\Request;


Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    # PRODUCT
    Route::get('/products/amazon.com/{id}', 'API\\ProductController@inspect');
    # END PRODUCT

    # ACCOUNT
    Route::get('/user/accounts', 'AccountsController@myAccounts');
    Route::get('/accounts/{username}/profiles', 'API\\AccountController@profiles');
    Route::post('/accounts/{username}/items', 'API\\ListerController@submit');
    # END ACCOUNT

    # RAW REQUEST
    Route::post('accounts/{username}/trading/{method}', 'API\\TradingAPIController@send');
    # END RAW REQUEST

    # EBAY INTERACTIONS
    Route::group(['prefix' => 'accounts/{account}'], function () {
        # ADD NEW ITEM
        Route::post('add_item', 'Account\ItemsController@addItem');

        // Supports
        Route::get('inspect', 'Account\ItemsController@inspectSourceProduct');
        Route::get('suggest_category', 'Account\ItemsController@suggestCategory');
        Route::get('seller_profiles', 'Account\ItemsController@sellerProfiles');
        Route::get('allowed_conditions/{category_id}', 'Account\ItemsController@allowedConditions');
    });

    # TRACKING
    Route::group(['prefix' => 'item/{item}'], function () {
        Route::get('trackers', 'TrackersController@itemTrackers');
        Route::post('trackers', 'TrackersController@addTrackerForItem');
    });

    Route::delete('trackers/{tracker}', 'TrackersController@deleteTracker');
    # END TRACKING
# END EBAY INTERACTIONS
});