<?php

namespace App\Console\Commands;

use App\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

abstract class AccountSyncer extends Command
{
    public function handle()
    {
        $query = Account::query();

        if ($this->hasArgument('username')) {
            $query->where('username', $this->argument('username'));
        }

        $since = null;
        if ($this->hasOption('since')) {
            try {
                $since = new Carbon($this->option('since'));
            } catch (\Exception $exception) {
                $this->error('Don\'t understand the `since` value. Try again!');

                return;
            }
        }

        $query->get()->shuffle()->each(function (Account $account) use ($since) {
            // 1. Sync Items
            if ($this->option('only_items')) {
                $this->syncItems($account, $since);

                return;
            }

            // 2. Sync Orders
            if ($this->option('only_orders')) {
                $this->syncOrders($account, $since);

                return;
            }

            // 3. Sync both orders & item
            $this->syncAllAspects($account, $since);
        });
    }

    protected function syncAllAspects(Account $account, $since = null): void
    {
        $this->syncItems($account, $since);
        $this->syncOrders($account, $since);
    }

    protected function syncItems(Account $account, $since = null): void
    {
        $account->syncItemsByStartTimeRange(
            $since ?: Carbon::now()->subMonths(3),
            Carbon::now()
        );
    }

    protected function syncOrders(Account $account, $since = null): void
    {
        $account->syncOrdersByCreatedTimeRange(
            $since ?: Carbon::now()->subMonths(12),
            Carbon::now()
        );
    }
}