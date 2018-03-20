<?php

namespace App\Http\Controllers;

use App\Account;
use App\eBay\FindingAPI;
use App\Exceptions\FindingApiException;
use App\Item;
use App\Jobs\Amazon\ExtractOffers;
use App\Jobs\SyncAmazonProduct;
use App\Researching\CompetitorResearch;
use App\Sourcing\OfferListingExtractor;
use App\Support\SellingPriceCalculator;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\ItemFilterType;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\FindItemsByKeywordsRequest;
use DTS\eBaySDK\Finding\Types\ItemFilter;
use DTS\eBaySDK\Finding\Types\PaginationInput;
use DTS\eBaySDK\Shopping\Services\ShoppingService;
use DTS\eBaySDK\Shopping\Types\GetMultipleItemsRequestType;
use DTS\eBaySDK\Shopping\Types\GetMultipleItemsResponseType;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsRequestType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use Illuminate\Http\Request;

class ResearchController extends AuthRequiredController
{
    public function compare(Request $request)
    {
        $this->validate($request, ['ids' => 'required|min:8']);

        $ids = explode(',', $request['ids']);

        $response = $this->mappingItems($ids);

        $items  = $response->Item;
        $errors = $response->Errors;

        return view('research.compare', compact('items', 'errors'));
    }

    protected function mappingItems(array $ids): GetMultipleItemsResponseType
    {
        $shopping = app(ShoppingService::class);

        $request = new GetMultipleItemsRequestType;

        $request->ItemID = $ids;

        $request->IncludeSelector = 'Details';

        return $shopping->getMultipleItems($request);
    }

    protected function shopping(): ShoppingService
    {
        return app(ShoppingService::class);
    }

    protected function finding(): FindingService
    {
        return app(FindingService::class);
    }

    public function competitor(Request $request)
    {
        if ( ! $request->has('username')) {
            return view('research.competitor');
        }

        $this->validate($request, [
            'username'   => 'required',
            'date_range' => 'required|in:7,14,21,30,60,90',
        ]);

        $research = new CompetitorResearch($request['username'], $request['date_range']);

        $performance = $research->performance();

        $items = $research->items();

        return view('research.competitor', compact('performance', 'items'));
    }

    public function amazon(Request $request)
    {

    }

    public function asins(Request $request)
    {
        $this->validate($request, ['asins' => 'required']);

        $asins = explode(',', $request['asins']);

        $products = collect($asins)->unique()->map(function ($asin) {
            return $this->getProduct($asin);
        });

        return view('research.asins', compact('products'));
    }

    public function asin(Request $request, $asin)
    {
        $asin = $request['asin'];

        $product = $this->getProduct($asin);

        if (key_exists('UPC', $product['attributes'])) {
            $competitors = $this->geteBayCompetitors($product['attributes']['UPC']);
        } elseif (key_exists('EAN', $product['attributes'])) {
            $competitors = $this->geteBayCompetitors($product['attributes']['EAN']);
        } else {
            $competitors = null;
        }

        $costOfGoods     = $product['best_offer']['tax'] ? $product['best_offer']['price'] * 1.09 : $product['best_offer']['price'];
        $minSellingPrice = SellingPriceCalculator::calc([
            'cost_of_goods'    => $product['best_offer']['price'],
            'margin'           => 0,
            'tax'              => $product['best_offer']['tax'],
            'final_value_rate' => 0.0915,
            'paypal_rate'      => 0.039,
            'paypal_usd'       => 0.3,
            'minimum_price'    => 0.0,
        ]);

        if ($competitors) {
            $higherPrice = collect($competitors->searchResult->toArray()['item'])
                ->where('sellingStatus.currentPrice.value', '>', $minSellingPrice)
                ->count();

            $equalsPrice = collect($competitors->searchResult->toArray()['item'])
                ->where('sellingStatus.currentPrice.value', '=', $minSellingPrice)
                ->count();

            $lowerPrice = collect($competitors->searchResult->toArray()['item'])
                ->where('sellingStatus.currentPrice.value', '<', $minSellingPrice)
                ->count();

            $soldLastThirtyDays = [];

            foreach ($competitors->searchResult->item as $item) {
                $soldLastThirtyDays[$item->itemId] = $this->soldLastThirtyDays($item->itemId) ?: null;
            }
        }

        return view(
            'research.asin',
            compact(
                'product', 'competitors', 'higherPrice', 'equalsPrice', 'lowerPrice', 'costOfGoods',
                'minSellingPrice', 'soldLastThirtyDays'
            )
        );
    }

    protected function getProduct($asin)
    {
        $product = cache()->remember("amazon:{$asin}", 60, function () use ($asin) {
            return SyncAmazonProduct::dispatchNow($asin);
        });

        $product['offers'] = @$product['offers'] ?: cache()->remember("amazon:{$asin}:offers", 60,
            function () use ($asin) {
                return ExtractOffers::dispatchNow($asin);
            });

        $product['offers'] = collect($product['offers'])->map(function ($offer) {
            return array_merge($offer, ['tax' => $offer['seller'] === 'Amazon.com']);
        })->all();

        $product['best_offer'] = OfferListingExtractor::bestOfferWithTax($product['offers']);

        $product['listed_on'] = Item::listedOn($asin)->get()->pluck('account.username');

        return $product;
    }

    protected function geteBayCompetitors($keyword)
    {
        /** @var FindingService|FindingAPI $finding */
        $finding = new FindingAPI;

        $request = new FindItemsByKeywordsRequest;

        $request->keywords        = (string)$keyword;
        $request->buyerPostalCode = (string)10001;

        $request->itemFilter[] = new ItemFilter(['name' => ItemFilterType::C_CONDITION, 'value' => ['New']]);

        $request->outputSelector = [
            OutputSelectorType::C_SELLER_INFO,
        ];

        $request->paginationInput = new PaginationInput;

        $request->paginationInput->entriesPerPage = 100;

        $response = $finding->findItemsByKeywords($request, 60);

        if ($response->ack === AckValue::C_FAILURE) {
            throw new FindingApiException($request, $response);
        }

        return $response;
    }

    protected function soldLastThirtyDays($itemID)
    {
        /** @var Account $account */
        $account = $this->resolveCurrentUser()->accounts()->inRandomOrder()->firstOrFail();

        $request         = new GetItemTransactionsRequestType;
        $request->ItemID = (string)$itemID;

        $request->Pagination                 = new PaginationType;
        $request->Pagination->EntriesPerPage = 1;

        $request->NumberOfDays = 30;

        $request->OutputSelector = [
            'PaginationResult.TotalNumberOfEntries',
        ];

        /** @var \DTS\eBaySDK\Trading\Services\TradingService $trading */
        $trading = $account->trading();

        $response = $trading->getItemTransactions($request, 60 * 6);

        return $response->PaginationResult->TotalNumberOfEntries;
    }
}
