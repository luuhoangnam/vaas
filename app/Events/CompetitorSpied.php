<?php

namespace App\Events;

use App\Spy\Competitor;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetitorSpied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $competitor;

    public function __construct(Competitor $competitor)
    {
        $this->competitor = $competitor;
    }
}
