<?php

namespace App\Listeners\Miner;

use App\Events\Miner\CompetitorItemCreated;
use App\Jobs\Miner\GetItem;

class GetItemDetails
{
    public function handle(CompetitorItemCreated $event)
    {
        GetItem::dispatch($event->item)->onQueue('miner.item.details');
    }
}
