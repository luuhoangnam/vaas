<?php

namespace App\eBay;

use App\Account;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\AbstractRequestType;
use DTS\eBaySDK\Trading\Types\AbstractResponseType;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use Illuminate\Contracts\Cache\Factory as Cache;

class TradingAPI extends API
{
    protected $token;

    protected $cache;

    protected $shouldCache = [
        '/^get.+$/i',
    ];

    public function __construct($token, Cache $cache = null)
    {
        $this->token = $token;
        $this->cache = $cache ?: app(Cache::class);
    }

    public static function make(Account $account): TradingAPI
    {
        return new static($account['token']);
    }

    protected function api()
    {
        return app(TradingService::class);
    }

    public function __call($method, $arguments)
    {
        if ( ! $arguments[0] instanceof AbstractRequestType) {
            return forward_static_call_array([$this->api(), $method], $arguments);
        }

        /** @var AbstractRequestType $request */
        $request = $arguments[0];

        // Credentials
        $request->RequesterCredentials = new CustomSecurityHeaderType (['eBayAuthToken' => $this->token]);

        $cacheTime = isset($arguments[1]) ? (float)$arguments[1] : config('ebay.api.cache_time', 1);

        if ($this->isCached($method) && $cacheTime) {
            $cacheKey = md5(serialize($request->toArray()));

            $data = cache()->remember($cacheKey, $cacheTime, function () use ($method, $request) {
                /** @var AbstractResponseType $response */
                $response = $this->forward($method, $request);

                return $response->toArray();
            });

            $responseClass = '\\DTS\\eBaySDK\\Trading\\Types\\' . studly_case($method) . 'ResponseType';

            return new $responseClass($data);
        }

        return $this->forward($method, $request);
    }
}