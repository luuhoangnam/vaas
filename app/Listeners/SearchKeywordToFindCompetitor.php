<?php

namespace App\Listeners;

use App\Events\SeedKeywordAdded;
use App\Jobs\FindCompetitorItemTask;
use App\Jobs\FindCompetitorsByKeyword;

class SearchKeywordToFindCompetitor
{
    public function __construct()
    {
        //
    }

    public function handle(SeedKeywordAdded $event)
    {
        FindCompetitorsByKeyword::dispatch($event->keyword['keyword']);
    }
}
