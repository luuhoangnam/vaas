<?php

namespace App\Console\Commands;

use App\Jobs\UpdateAPIUsage;
use Illuminate\Console\Command;

class UpdateeBayAPIQuota extends Command
{
    protected $signature = 'api:quota';

    protected $description = 'Update API Quota in Cache and Firebase';

    public function handle()
    {
        UpdateAPIUsage::dispatchNow();
    }
}
