<?php

use Illuminate\Http\Request;


Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    # ACCOUNT
    Route::get('/user/accounts', 'AccountsController@myAccounts');
    # END ACCOUNT

    # RAW REQUEST
    Route::post('accounts/{username}/raw/{method}', 'API\RawController@send');
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