<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\Http\Controllers\AuthRequiredController;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\AbstractRequestType;
use DTS\eBaySDK\Trading\Types\AbstractResponseType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TradingAPIController extends AuthRequiredController
{
    public function send(Request $request, $username, $method)
    {
        $account = Account::find($username);

        $this->authorize('trading', $account);

        $requestClass = $this->requestClass($method);

        if ( ! $this->allowToCall($method) || ! class_exists($requestClass)) {
            return new Response(['error' => 'Request Does Not Supported'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            /** @var AbstractRequestType $payload */
            $payload = $account->prepareAuthRequiredRequest(new $requestClass($request->all()));

            $cacheKey  = md5(serialize($payload->toArray()));
            $cacheTime = $request->header('X-CACHE-TIME', $this->cacheTime($method));

            if ($cacheTime === 'false' || $cacheTime === '0' || $cacheTime === 'no') {
                cache()->forget($cacheKey);
            }

            $data = cache()->remember($cacheKey, $cacheTime, function () use ($payload, $account, $method) {
                return $account->trading()->$method($payload)->toArray();
            });

            $responseClass = $this->responseClass($method);

            /** @var AbstractResponseType $response */
            $response = new $responseClass($data);

            if ($response->Ack !== AckCodeType::C_SUCCESS) {
                return new Response($response->toArray(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return $response->toArray();
        } catch (\Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }

    protected function cacheTime($method)
    {
        return config("ebay.call_forwarding.trading.{$method}.cache_time", 0);
    }

    protected function allowToCall($method): bool
    {
        $allows = config('ebay.call_forwarding.trading');

        return in_array($method, $allows) || in_array($method, array_keys($allows));
    }

    protected function responseClass($method): string
    {
        return "DTS\\eBaySDK\\Trading\\Types\\" . studly_case($method) . "ResponseType";
    }

    protected function requestClass($method): string
    {
        return "\\DTS\\eBaySDK\\Trading\\Types\\" . studly_case($method) . 'RequestType';
    }
}
