<?php

namespace App\Http\Controllers;

use App\Jobs\Amazon\ExtractOffers;
use App\Jobs\SyncAmazonProduct;
use App\Researching\CompetitorResearch;
use App\Sourcing\AmazonAPI;
use App\Sourcing\OfferListingExtractor;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Shopping\Services\ShoppingService;
use DTS\eBaySDK\Shopping\Types\GetMultipleItemsRequestType;
use DTS\eBaySDK\Shopping\Types\GetMultipleItemsResponseType;
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
            $product = cache()->remember("amazon:{$asin}", 60, function () use ($asin) {
                return SyncAmazonProduct::dispatchNow($asin);
            });

            $product['offers'] = @$product['offers'] ?: cache()->remember("amazon:{$asin}:offers", 60,
                function () use ($asin) {
                    return ExtractOffers::dispatchNow($asin);
                });

            $product['best_offer'] = OfferListingExtractor::bestOfferWithTax($product['offers']);

            return $product;
        });

        return view('research.asins', compact('products'));
    }
}
