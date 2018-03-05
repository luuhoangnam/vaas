<?php

namespace App\Console\Commands;

class SynceBayAccount extends AccountSyncer
{
    protected $signature = 'ebay:sync {username} {--only_orders} {--only_items}';

    protected $description = 'Sync All eBay Items & Orders';
}
