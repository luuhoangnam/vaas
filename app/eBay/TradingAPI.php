<?php

namespace App\eBay;

use App\Account;
use App\Exceptions\TradingApiException;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Enums\DetailLevelCodeType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Trading\Types\GetApiAccessRulesRequestType;
use DTS\eBaySDK\Trading\Types\ItemType;
use Illuminate\Contracts\Cache\Factory as Cache;
use Illuminate\Database\Eloquent\Builder;

class TradingAPI extends API
{
    protected $token;

    protected $shouldCache = [
        '/^get.+$/i',
    ];

    public function __construct($token, Cache $cache = null)
    {
        $this->token = $token;

        parent::__construct($cache);
    }

    public static function make(Account $account): TradingAPI
    {
        return new static($account['token']);
    }

    protected function api()
    {
        return app(TradingService::class);
    }

    protected function responseClass(string $method): string
    {
        return '\\DTS\\eBaySDK\\Trading\\Types\\' . studly_case($method) . 'ResponseType';
    }

    protected function prepare($request)
    {
        $request->RequesterCredentials = new CustomSecurityHeaderType (['eBayAuthToken' => $this->token]);

        return $request;
    }

    public static function normalizeAttribute(ItemType $item)
    {
        $attributes = [];

        foreach ($item->ItemSpecifics->NameValueList as $specific) {
            $attributes[$specific->Name] = $specific->Value[0];
        }

        return $attributes;
    }

    public static function listedOn($sku)
    {
        return Account::query()
                      ->whereHas('items', function (Builder $builder) use ($sku) {
                          $builder->where('sku', $sku);
                      })
                      ->get();
    }

    public function usage($method = 'ApplicationAggregate')
    {
        $request = new GetApiAccessRulesRequestType;

        $request->DetailLevel = [DetailLevelCodeType::C_RETURN_ALL];

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->getApiAccessRules($request, false);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }

        foreach ($response->ApiAccessRule as $rule) {
            if ($rule->CallName === $method) {
                return [$rule->DailyUsage, $rule->DailySoftLimit, $rule->DailyHardLimit];
            }
        }

        return null;
    }
}