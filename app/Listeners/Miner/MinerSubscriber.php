<?php

namespace App\Listeners\Miner;

use App\Events\Miner\CompetitorCreated;
use App\Events\Miner\CompetitorItemCreated;
use App\Jobs\FetchCompetitorItems;
use App\Jobs\Miner\GetItem;
use App\Jobs\Miner\GetItemTransactions;
use Illuminate\Events\Dispatcher;

class MinerSubscriber
{
    public function subscribe(Dispatcher $events)
    {
        #1.
        $events->listen(CompetitorCreated::class, [$this, 'searchItems']);

        #2.
        $events->listen(CompetitorItemCreated::class, [$this, 'getItem']);

        #3.
        $events->listen(CompetitorItemCreated::class, [$this, 'getPerformance']);
    }

    public function searchItems(CompetitorCreated $event)
    {
        FetchCompetitorItems::dispatch($event->competitor)->onQueue('miner.competitor.items');
    }

    public function getItem(CompetitorItemCreated $event)
    {
        // Get Item Details if it qualified
        $qualifiedPrice = config('miner.criterias.min_price', 10);

        if ($event->item->price >= $qualifiedPrice) {
            GetItem::dispatch($event->item)->onQueue('miner.item.details');
        }
    }

    public function getPerformance(CompetitorItemCreated $event)
    {
        // Get Item Details if it qualified
        $qualifiedPrice = config('miner.criterias.min_price', 10);

        if ($event->item->price >= $qualifiedPrice) {
            GetItemTransactions::dispatch($event->item)->onQueue('miner.item.performance');
        }
    }
}