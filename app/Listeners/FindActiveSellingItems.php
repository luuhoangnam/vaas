<?php

namespace App\Listeners;

use App\Events\CompetitorSpied;
use App\Jobs\FindItemAdvanced;

class FindActiveSellingItems
{
    public function handle(CompetitorSpied $event)
    {
        $username = $event->competitor['username'];

        FindItemAdvanced::dispatchNow($username);
    }
}
