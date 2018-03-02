<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {

    $username = 'goodie.depot';

    $account = \App\Account::find($username);

    $response = $account->addItem([
        'title'               => 'Bella BLA14485 One Scoop One Cup Coffee Maker, Red and Stainless Steel',
        'category_id'         => '42255',
        'payment_profile_id'  => 122809677013,
        'shipping_profile_id' => 122809716013,
        'return_profile_id'   => 122809736013,
        'price'               => 30.00,
        'description'         => 'Description For Bella BLA14485 One Scoop One Cup Coffee Maker, Red and Stainless Steel',
        'condition_id'        => 1000,
        'quantity'            => 1,
        'pictures'            => [
            'https://s3-us-west-2.amazonaws.com/vaas-assets/1.jpg',
            'https://s3-us-west-2.amazonaws.com/vaas-assets/2.jpg',
            'https://s3-us-west-2.amazonaws.com/vaas-assets/3.jpg',
        ],
    ]);

    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');