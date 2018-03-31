<?php

namespace App\Events;

use App\Spy\SeedKeyword;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeedKeywordAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $keyword;

    public function __construct(SeedKeyword $keyword)
    {
        $this->keyword = $keyword;
    }
}
