<?php

namespace App\Jobs\Miner;

use App\eBay\TradingAPI;
use App\Miner\Item;
use Carbon\Carbon as PureCarbon;
use DTS\eBaySDK\Trading\Enums\DetailLevelCodeType;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsRequestType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use DTS\eBaySDK\Trading\Types\TransactionType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GetItemTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function handle()
    {
        $request = new GetItemTransactionsRequestType;

        $request->ItemID       = (string)$this->item->item_id;
        $request->Pagination   = new PaginationType(['EntriesPerPage' => 100, 'PageNumber' => 1]);
        $request->NumberOfDays = 30;

        $request->DetailLevel = [DetailLevelCodeType::C_RETURN_ALL];

        $transactions = new Collection;

        do {
            $response = TradingAPI::random()->getItemTransactions($request);

            if ($response->PaginationResult->TotalNumberOfEntries === 0) {
                foreach ([7, 14, 21, 30] as $period) {
                    $this->item->updatePerformance($period, 0, 0);
                }

                return;
            }

            if ($response->TransactionArray->Transaction) {
                foreach ($response->TransactionArray->Transaction as $transaction) {
                    $transactions->push($transaction);
                }
            }

            $request->Pagination->PageNumber++;
        } while ($response->HasMoreTransactions);

        // Transaction Last 30 Days
        $periods = [7, 14, 21, 30];

        foreach ($periods as $period) {
            $since = Carbon::now()->subDays($period);
            $until = Carbon::now();

            $performance = $this->transactionDetailsForPeriod($transactions, $since, $until);

            $this->item->updatePerformance($period, $performance['count'], $performance['revenue']);
        }
    }

    protected function transactionDetailsForPeriod(Collection $transactions, PureCarbon $since, PureCarbon $until)
    {
        $filtered = $transactions->filter(function (TransactionType $transaction) use ($since, $until) {
            return app_carbon($transaction->CreatedDate)->between($since, $until);
        })->map(function (TransactionType $transaction) {
            return $transaction->toArray();
        });

        $period           = $until->diffInDays($since);
        $count            = $filtered->count();
        $revenue          = round_even($filtered->sum('TransactionPrice.value'));
        $quantity         = $filtered->sum('QuantityPurchased');
        $average_price    = $count ? round_even($revenue / $count) : 0;
        $average_quantity = $count ? round_even($quantity / $count) : 0;

        return compact('period', 'count', 'revenue', 'quantity', 'average_price', 'average_quantity');
    }
}
