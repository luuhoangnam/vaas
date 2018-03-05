<?php

namespace App\Console\Commands;

class SyncAlleBayAccounts extends AccountSyncer
{
    protected $signature = 'ebay:sync:all {--only_orders} {--only_items}';

    protected $description = 'Sync Items & Orders for all eBay Accounts';
}
