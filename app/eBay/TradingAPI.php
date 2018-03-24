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

    public static function getItem($id, Account $account = null)
    {
        $account = $account instanceof Account ? $account : Account::random();

        $trading = $request = new GetItemRequestType;

        $request->IncludeWatchCount    = true;
        $request->IncludeItemSpecifics = true;

        $request->ItemID = (string)$id;

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $response = $account->trading()->getItem($request, 15);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }

        $item = $response->Item;

        return [
            'item_id'              => $item->ItemID,
            'title'                => $item->Title,
            'primary_category'     => [
                'id'      => (int)$item->PrimaryCategory->CategoryID,
                'name'    => $item->PrimaryCategory->CategoryName,
                'parents' => array_reverse(explode(':', $item->PrimaryCategory->CategoryName)),
            ],
            'picture'              => $item->PictureDetails->GalleryURL,
            'country'              => $item->Country,
            'price'                => $item->SellingStatus->CurrentPrice->value,
            'currency'             => $item->SellingStatus->CurrentPrice->currencyID,
            'status'               => $item->SellingStatus->ListingStatus,
            'quantity'             => $item->Quantity,
            'quantity_sold'        => $item->SellingStatus->QuantitySold,
            'sku'                  => $item->SKU,
            'postal_code'          => $item->PostalCode,
            'handling_time'        => $item->DispatchTimeMax,
            'start_time'           => app_carbon($item->ListingDetails->StartTime)->toDateTimeString(),
            'end_time'             => app_carbon($item->ListingDetails->EndTime)->toDateTimeString(),
            'listing_type'         => $item->ListingType,
            'condition'            => $item->ConditionDisplayName,
            'has_variants'         => (bool)$item->Variations,
            'is_top_rated_listing' => $item->TopRatedListing,
            'watch_count'          => $item->WatchCount,
            'attributes'           => static::normalizeAttribute($item),
            'listed_on'            => static::listedOn($item->SKU),
        ];
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