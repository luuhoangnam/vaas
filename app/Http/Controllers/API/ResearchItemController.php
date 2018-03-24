<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\Exceptions\Amazon\ProductAdvertisingAPIException;
use App\Exceptions\TradingApiException;
use App\Http\Controllers\Controller;
use App\Item;
use App\Sourcing\AmazonAPI;
use App\Sourcing\AmazonCrawler;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\GetItemRequestType;
use DTS\eBaySDK\Trading\Types\ItemType;
use Illuminate\Http\Request;

class ResearchItemController extends Controller
{
    public function show(Request $request, $id)
    {
        $request = new GetItemRequestType;

        $request->IncludeWatchCount    = true;
        $request->IncludeItemSpecifics = true;

        $request->ItemID = (string)$id;

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $response = $this->trading()->getItem($request, 15);

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
            'attributes'           => $this->normalizeAttribute($item),
            'source'               => $this->guessSource($item),
        ];
    }

    protected function normalizeAttribute(ItemType $item)
    {
        $attributes = [];

        foreach ($item->ItemSpecifics->NameValueList as $specific) {
            $attributes[$specific->Name] = $specific->Value[0];
        }

        return $attributes;
    }

    protected function trading()
    {
        return Account::random()->trading();
    }

    protected function guessSource(ItemType $item)
    {
        $cacheKey  = md5("items:{$item->ItemID}:source");
        $cacheTime = 60;

        return cache()->remember($cacheKey, $cacheTime, function () use ($item) {
            if ( ! $item->SKU) {
                return null;
            }

            try {
                $product = AmazonAPI::inspect($item->SKU, true);

                return $product;
            } catch (ProductAdvertisingAPIException $exception) {
                if ($exception->getCode() !== 'AWS.ECommerceService.ItemNotAccessible') {
                    return null;
                }
            }

            return AmazonCrawler::get($item->SKU);
        });
    }
}
