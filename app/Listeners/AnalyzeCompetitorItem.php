<?php

namespace App\Listeners;

use App\Events\FoundNewCompetitorItem;
use App\Jobs\UpdateItemSellingPerformance;

class AnalyzeCompetitorItem
{
    public function handle(FoundNewCompetitorItem $event)
    {
        UpdateItemSellingPerformance::dispatchNow($event->item);
    }
}
