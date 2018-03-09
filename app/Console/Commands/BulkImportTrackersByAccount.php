<?php

namespace App\Console\Commands;

use App\Account;
use App\Item;
use Illuminate\Console\Command;

class BulkImportTrackersByAccount extends Command
{
    protected $signature = 'tracker:import {username}';

    protected $description = 'Bulk import trackers for each item in account. Tracker will by default track item title';

    public function handle()
    {
        $items = Account::find($this->argument('username'))
                        ->items()
                        ->has('trackers', '=', 0) // Void the already tracked item
                        ->get(['id', 'item_id', 'title']);

        $items->shuffle()->each(function (Item $item) {
            $item->track($item['title']);
        });

        $this->info('Done!');
    }
}
