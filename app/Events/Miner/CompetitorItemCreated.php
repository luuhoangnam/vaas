<?php

namespace App\Events\Miner;

use App\Miner\Item;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetitorItemCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }
}
