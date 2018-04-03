<?php

namespace App\Listeners\Miner;

use App\Events\Miner\CompetitorItemCreated;
use App\Jobs\Miner\GetItemTransactions;

class GetItemSellingPerformance
{
    public function handle(CompetitorItemCreated $event)
    {
        GetItemTransactions::dispatch($event->item)->onQueue('miner.item.performance');
    }
}
