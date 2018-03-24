<?php

namespace App\eBay;

use App\Account;
use App\Exceptions\TradingApiException;
use App\Item;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Trading\Types\GetItemRequestType;
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
}