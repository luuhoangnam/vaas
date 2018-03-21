<?php

namespace App\Console\Commands;

use App\Jobs\RefreshRank as RefreshRankJob;
use App\Ranking\Tracker;
use Illuminate\Console\Command;

class PeriodicRefreshRank extends Command
{
    protected $signature = 'ranking:refresh';

    public function handle()
    {
        Tracker::all()->shuffle()->each(function (Tracker $tracker) {
            RefreshRankJob::dispatch($tracker)->onQueue('ranking');
        });
    }
}
