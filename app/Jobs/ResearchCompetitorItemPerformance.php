<?php

namespace App\Jobs;

use App\Account;
use App\Exceptions\TradingApiException;
use App\Spy\CompetitorItem;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsRequestType;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsResponseType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use DTS\eBaySDK\Trading\Types\TransactionType;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Carbon\Carbon as PureCarbon;

class ResearchCompetitorItemPerformance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $item;

    public function __construct(CompetitorItem $item)
    {
        $this->item = $item;
    }

    public function handle()
    {
        $request = new GetItemTransactionsRequestType;

        $request->ItemID = (string)$this->item['item_id'];

        $request->Pagination                 = new PaginationType;
        $request->Pagination->EntriesPerPage = 100;

        $request->NumberOfDays = 30;

        $response = $this->trading()->getItemTransactions($request);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }

        try {
            $transactions = $this->transactions($response);
        } catch (\Exception $exception) {
            throw $exception;
        }

        // Transaction Last 30 Days
        $now     = Carbon::now();
        $sold7d  = $this->countTransactionForPeriod($transactions, (clone $now)->subDays(7), $now);
        $sold14d = $this->countTransactionForPeriod($transactions, (clone $now)->subDays(14), $now);
        $sold21d = $this->countTransactionForPeriod($transactions, (clone $now)->subDays(21), $now);
        $sold30d = $this->countTransactionForPeriod($transactions, (clone $now)->subDays(30), $now);

        $this->item->update([
            'sold_7d'         => $sold7d,
            'sold_14d'        => $sold14d,
            'sold_21d'        => $sold21d,
            'sold_30d'        => $sold30d,
            'perf_updated_at' => Carbon::now(),
        ]);
    }

    public function trading()
    {
        return Account::random()->trading();
    }

    protected function countTransactionForPeriod(Collection $transactions, PureCarbon $since, PureCarbon $until): int
    {
        return $transactions
            ->filter(function (TransactionType $transaction) use ($since, $until) {
                return app_carbon($transaction->CreatedDate)->between($since, $until);
            })
            ->count();
    }

    protected function transactions(GetItemTransactionsResponseType $response): Collection
    {
        if (is_null(@$response->TransactionArray->Transaction)) {
            return new Collection;
        }

        return new Collection($response->TransactionArray->Transaction);
    }
}
