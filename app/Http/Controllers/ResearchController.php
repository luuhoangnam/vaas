<?php

namespace App\Http\Controllers;

use App\Account;
use DTS\eBaySDK\Shopping\Services\ShoppingService;
use DTS\eBaySDK\Shopping\Types\GetMultipleItemsRequestType;
use DTS\eBaySDK\Shopping\Types\GetMultipleItemsResponseType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
}
