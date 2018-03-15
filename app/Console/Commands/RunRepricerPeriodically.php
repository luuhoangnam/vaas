<?php

namespace App\Console\Commands;

use App\Jobs\RunRepricer;
use App\Repricing\Repricer;
use Illuminate\Console\Command;

class RunRepricerPeriodically extends Command
{
    protected $signature = 'repricer:periodic';

    protected $description = 'Run Repricer Periodically with Queue';

    public function handle()
    {
        Repricer::query()->get()->shuffle()->each(function (Repricer $repricer) {
            RunRepricer::dispatch($repricer);
        });
    }
}
