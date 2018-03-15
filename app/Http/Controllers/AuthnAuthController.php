<?php

namespace App\Http\Controllers;

use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\FetchTokenRequestType;
use DTS\eBaySDK\Trading\Types\GetSessionIDRequestType;
use Illuminate\Http\Request;

class AuthnAuthController extends AuthRequiredController
{
    public function signIn(Request $request)
    {
        $ruName = env('EBAY_RUNAME');

        $sessionId = $this->getSessionID($ruName);

        $request->session()->flash('ebay_session_id', $sessionId);

        $url = "https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&RuName={$ruName}&SessID={$sessionId}";

        return redirect($url);
    }

    public function callback(Request $request)
    {
        $sessionID = $request->session()->get('ebay_session_id');

        if ( ! $sessionID) {
            abort(403, 'Missing SessionID');
        }

        $token = $this->fetchToken($sessionID);

        $username = $request['username'];

        $request->user()->addAccount($username, $token);

        return redirect()->route('home');
    }

    protected function trading(): TradingService
    {
        return app(TradingService::class);
    }

    protected function fetchToken($sessionID): string
    {
        $request = new FetchTokenRequestType;

        $request->SessionID = $sessionID;

        $response = $this->trading()->fetchToken($request)->toArray();

        return $response['eBayAuthToken'];
    }

    protected function getSessionID($ruName): string
    {
        $response = $this->trading()
                         ->getSessionID(new GetSessionIDRequestType(['RuName' => $ruName,]))
                         ->toArray();

        return $response['SessionID'];
    }
}
