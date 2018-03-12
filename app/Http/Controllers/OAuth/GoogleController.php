<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\AuthRequiredController;
use Google_Client;
use Google_Service_Gmail;
use Illuminate\Http\Request;

class GoogleController extends AuthRequiredController
{
    public function redirect(Request $request, Google_Client $client)
    {
        return redirect($client->createAuthUrl());
    }

    public function callback(Request $request, Google_Client $client)
    {
        if ( ! $request->has('code')) {
            return redirect()->route('home');
        }

        $data = $client->fetchAccessTokenWithAuthCode($request['code']);

        $user = $this->resolveCurrentUser();

        $client->setAccessToken($data['access_token']);

        $service = new Google_Service_Gmail($client);

        $email = $service->users->getProfile('me')->getEmailAddress();

        $account = $user->googleAccounts()->create([
            'email'         => $email,
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
        ]);

        return redirect()->route('home');
    }
}
