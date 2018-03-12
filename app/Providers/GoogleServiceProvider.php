<?php

namespace App\Providers;

use Google_Client;
use Google_Service_Gmail;
use Google_Service_Oauth2;
use Illuminate\Support\ServiceProvider;

class GoogleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Google_Client::class, function () {
            $client = new Google_Client([
                'client_id'     => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect_uri'  => 'https://bc232b84.ngrok.io/oauth/google/callback',
            ]);

            $client->setAccessType('offline');

            $client->setScopes([
                Google_Service_Oauth2::USERINFO_EMAIL,
                Google_Service_Gmail::MAIL_GOOGLE_COM,
            ]);

            return $client;
        });
    }
}
