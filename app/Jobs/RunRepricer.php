<?php

namespace App\Jobs;

use App\Repricing\Repricer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RunRepricer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $repricer;

    public function __construct(Repricer $repricer)
    {
        $this->repricer = $repricer;
    }

    public function handle()
    {
        $this->repricer->run();
    }
}
