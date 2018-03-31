<?php

namespace App\Listeners;

use App\Events\CompetitorSpied;
use App\Jobs\FindCompetitorItemTask;

class FindCompetitorItems
{
    public function handle(CompetitorSpied $event)
    {
        FindCompetitorItemTask::dispatch($event->competitor);
    }
}
