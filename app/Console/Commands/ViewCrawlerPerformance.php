<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ViewCrawlerPerformance extends Command
{
    protected $signature = 'crawler:kpi';

    protected $description = 'View Crawler KPI';

    public function handle()
    {
        $total     = Redis::get('crawler:amazon:requests');
        $fails     = Redis::get('crawler:amazon:fails');
        $failsRate = percent($fails / $total);

        $this->info("Total Requests: {$total}");
        $this->info("Failed Requests: {$fails} ($failsRate)");
    }
}