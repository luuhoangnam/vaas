<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\eBay\TradingAPI;
use App\Exceptions\TradingApiException;
use App\Http\Controllers\AuthRequiredController;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsRequestType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use Illuminate\Http\Request;

class ItemController extends AuthRequiredController
{
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
        $request         = new GetItemTransactionsRequestType;
        $request->ItemID = (string)$itemID;

        $request->Pagination                 = new PaginationType;
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
}
