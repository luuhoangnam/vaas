<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\Http\Controllers\AuthRequiredController;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\AbstractResponseType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RawController extends AuthRequiredController
{
    public function send(Request $request, $username, $method)
    {
        $account = Account::find($username);

        $this->authorize('raw', $account);

        $requestClass = "\\DTS\\eBaySDK\\Trading\\Types\\" . studly_case($method) . 'RequestType';

        if ( ! class_exists($requestClass)) {
            return ['error' => 'Request Does Not Supported'];
        }

        try {
            $payload = $account->prepareAuthRequiredRequest(new $requestClass($request->all()));

            /** @var AbstractResponseType $response */
            $response = $account->trading()->$method($payload);

            if ($response->Ack !== AckCodeType::C_SUCCESS) {
                return new Response($response->toArray(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return $response->toArray();
        } catch (\Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }
}
