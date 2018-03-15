<?php

namespace App\Events;

use App\Item;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }
}
