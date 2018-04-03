<?php

namespace App\Events\Miner;

use App\Miner\Competitor;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetitorCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $competitor;

    public function __construct(Competitor $competitor)
    {
        $this->competitor = $competitor;
    }
}
