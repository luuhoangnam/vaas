<?php

namespace App\Listeners;

use App\Events\ItemCreated;

class AttachTracker
{
    public function handle(ItemCreated $event)
    {
        $event->item->track($event->item['title']);
    }
}
