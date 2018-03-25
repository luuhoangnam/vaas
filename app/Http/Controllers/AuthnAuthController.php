<?php

namespace App\Http\Controllers;

use DTS\eBaySDK\Constants\SiteIds;
use DTS\eBaySDK\Sdk;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\FetchTokenRequestType;
use DTS\eBaySDK\Trading\Types\GetSessionIDRequestType;
use Illuminate\Http\Request;

class AuthnAuthController extends AuthRequiredController
{
    public function signIn(Request $request)
    {
        $ruName = request('ru_name') ?: env('EBAY_RUNAME');

        $sessionId = $this->getSessionID($ruName);

        $request->session()->flash('ebay_session_id', $sessionId);

        $url = "https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&RuName={$ruName}&SessID={$sessionId}";

        return redirect($url);
    }

    public function callback(Request $request)
    {
        $sessionID = $request->session()->get('ebay_session_id');
        $isReturn  = $request->session()->get('ebay_return_token', false);

        if ( ! $sessionID) {
            abort(403, 'Missing SessionID');
        }

        $token = $this->fetchToken($sessionID);

        if ($isReturn) {
            return $token;
        }

        $username = $request['username'];

        $request->user()->addAccount($username, $token);

        return redirect()->route('home');
    }

    protected function trading(): TradingService
    {
        if (request()->has(['app_id', 'cert_id', 'dev_id', 'ru_name'])) {
            request()->session()->flash('ebay_return_token', true);

            return $this->makeSDK()->createTrading();
        }

        return app(TradingService::class);
    }

    protected function makeSDK()
    {
        return new Sdk([
            'siteId'      => SiteIds::US,
            'credentials' => [
                'appId'  => request('app_id'),
                'certId' => request('cert_id'),
                'devId'  => request('dev_id'),
                'ruName' => request('ru_name'),
            ],
            'Finding'     => [
                'apiVersion' => '1.13.0', // Release: 2014-10-21
            ],
            'Shopping'    => [
                'apiVersion' => '1027', // Release: 2017-Aug-04
            ],
            'Trading'     => [
                'apiVersion' => '1047', // Release: 2018-Feb-02
            ],
        ]);
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
