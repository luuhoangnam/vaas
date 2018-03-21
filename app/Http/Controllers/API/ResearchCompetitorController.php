<?php

namespace App\Http\Controllers\API;

use App\eBay\FindingAPI;
use App\Exceptions\FindingApiException;
use App\Exceptions\TradingApiException;
use App\Http\Controllers\AuthRequiredController;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\ItemFilterType;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\FindItemsAdvancedRequest;
use DTS\eBaySDK\Finding\Types\ItemFilter;
use DTS\eBaySDK\Finding\Types\PaginationInput;
use DTS\eBaySDK\Finding\Types\SearchItem;
use DTS\eBaySDK\Finding\Types\SearchResult;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsRequestType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ResearchCompetitorController extends AuthRequiredController
{
    public function items(Request $request)
    {
        $this->validate($request, [
            'username'   => 'required',
            'date_range' => 'required|numeric|in:7,14,30',
            'page'       => 'numeric|min:1',
        ]);

        $response = $this->search($request['username'], (int)$request['page']);

        $items = $this->normalizeItems($response->searchResult);

        $pagination = $response->paginationOutput;

        return [
            'data'         => $items,
            // Paginations
            'current_page' => $pagination->pageNumber,
            'last_page'    => $pagination->totalPages,
            'total'        => $pagination->totalEntries,
        ];
    }

    /**
     * @return FindingService|FindingAPI
     */
    protected function finding()
    {
        return new FindingAPI;
    }

    protected function search($username, $page = 1, $perPage = 25)
    {
        $request = new FindItemsAdvancedRequest;

        $request->itemFilter[] = new ItemFilter(['name' => ItemFilterType::C_SELLER, 'value' => [$username]]);

        $request->paginationInput = new PaginationInput;

        $request->paginationInput->entriesPerPage = $perPage;
        $request->paginationInput->pageNumber     = $page;

        $request->outputSelector = [
            OutputSelectorType::C_SELLER_INFO,
        ];

        $response = $this->finding()->findItemsAdvanced($request);

        if ($response->ack === AckValue::C_FAILURE) {
            throw new FindingApiException($request, $response);
        }

        return $response;
    }

    protected function normalizeItems(SearchResult $result): Collection
    {
        return collect($result->item)->map(function (SearchItem $item) {
            return [
                'item_id'           => $item->itemId,
                'title'             => $item->title,
                'primary_category'  => [
                    'id'   => optional($item->primaryCategory)->categoryId,
                    'name' => optional($item->primaryCategory)->categoryName,
                ],
                'gallery_url'       => $item->galleryURL,
                'country'           => $item->country,
                'shipping'          => [
                    'type'          => optional($item->shippingInfo)->shippingType,
                    'handling_time' => optional($item->shippingInfo)->handlingTime,
                    'expedited'     => optional($item->shippingInfo)->expeditedShipping,
                ],
                'returns_accepted'  => $item->returnsAccepted,
                'price'             => optional($item->sellingStatus)->currentPrice->value,
                'currency'          => optional($item->sellingStatus)->currentPrice->currencyId,
                'status'            => optional($item->sellingStatus)->sellingState,
                'start_time'        => app_carbon($item->listingInfo->startTime)->toDateTimeString(),
                'end_time'          => app_carbon($item->listingInfo->endTime)->toDateTimeString(),
                'listing_type'      => optional($item->listingInfo)->listingType,
                'condition'         => optional($item->condition)->conditionDisplayName,
                'has_variants'      => $item->isMultiVariationListing,
                'top_rated_listing' => $item->topRatedListing,
            ];
        });
    }
}
