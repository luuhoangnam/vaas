<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\eBay\TradingAPI;
use App\Exceptions\TradingApiException;
use App\Http\Controllers\AuthRequiredController;
use App\Http\Controllers\Controller;
use App\Item;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsRequestType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'listedStatus']);
    }

    public function transactionsCount(Request $request, $item)
    {
        $this->validate($request, [
            'date_range' => 'numeric|max:30',
        ]);

        $dateRange = $request->get('date_range', 30);

        $count = $this->countTransactions($item, $dateRange);

        return compact('count');
    }

    /**
     * @return TradingService|TradingAPI
     */
    protected function trading()
    {
        return Account::random()->trading();
    }

    protected function countTransactions($itemID, $dateRange = 30)
    {
        $request = new GetItemTransactionsRequestType;
        $request->ItemID = (string)$itemID;

        $request->Pagination = new PaginationType;
        $request->Pagination->EntriesPerPage = 1;

        $request->NumberOfDays = (int)$dateRange;

        $request->OutputSelector = [
            'PaginationResult.TotalNumberOfEntries',
            //            'Item.SKU',
        ];

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $response = $this->trading()->getItemTransactions($request, 60 * 24);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }

        return $response->PaginationResult->TotalNumberOfEntries;
    }

    public function listedStatus(Request $request)
    {
        $this->validate($request, [
            'asin' => 'nullable|required_without_all:upc,ean,isbn',
            'upc'  => 'nullable|required_without_all:asin,ean,isbn',
            'ean'  => 'nullable|required_without_all:asin,upc,isbn',
            'isbn' => 'nullable|required_without_all:asin,upc,ean',
        ]);

        $query = Item::query()->with('account');

        if ($asin = $request->get('asin')) {
            $query->where('sku', $asin);
        }

        if ($upc = $request->get('upc')) {
            $query->where('upc', $upc);
        }

        if ($ean = $request->get('ean')) {
            $query->where('ean', $ean);
        }

        if ($isbn = $request->get('isbn')) {
            $query->where('isbn', $isbn);
        }

        $items = $query->get();

        return response()->json($items, 200);
    }
}
