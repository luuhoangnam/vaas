<?php

namespace App\Listeners;

use App\Events\ListerJobCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateCompanionListerQueueJob implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ListerJobCreated $event)
    {
        //
    }
}
