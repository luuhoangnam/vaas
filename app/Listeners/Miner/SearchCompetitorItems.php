<?php

namespace App\Listeners\Miner;

use App\Events\Miner\CompetitorCreated;
use App\Jobs\FetchCompetitorItems;

class SearchCompetitorItems
{
    public function handle(CompetitorCreated $event)
    {
        FetchCompetitorItems::dispatch($event->competitor)->onQueue('miner.competitor.items');
    }
}
