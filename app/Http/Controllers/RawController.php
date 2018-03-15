<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;

class RawController extends Controller
{
    public function send(Request $request, Account $account, $name)
    {
        $requestClass = "\\DTS\\eBaySDK\\Trading\\Types\\" . studly_case($name) . 'RequestType';

        if ( ! class_exists($requestClass)) {
            return ['error' => 'Request Does Not Supported'];
        }

        try {
            $payload = $account->prepareAuthRequiredRequest(new $requestClass($request->all()));

            return $account->trading()->$name($payload)->toArray();
        } catch (\Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }
}
