<?php

namespace App\Events;

use App\Spy\CompetitorItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FoundNewCompetitorItem
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $item;

    public function __construct(CompetitorItem $item)
    {
        $this->item = $item;
    }
}
