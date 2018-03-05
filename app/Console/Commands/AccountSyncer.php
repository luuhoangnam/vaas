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

        $query->get()->shuffle()->each(function (Account $account) {
            // 1. Sync Items
            if ($this->option('only_items')) {
                $this->syncItems($account);

                return;
            }

            // 2. Sync Orders
            if ($this->option('only_orders')) {
                $this->syncOrders($account);

                return;
            }

            // 3. Sync both orders & items
            $this->syncAllAspects($account);
        });
    }

    protected function syncAllAspects(Account $account): void
    {
        $this->syncItems($account);
        $this->syncOrders($account);
    }

    protected function syncItems(Account $account): void
    {
        $account->syncItemsByStartTimeRange(
            Carbon::now()->subMonths(3),
            Carbon::now()
        );
    }

    protected function syncOrders(Account $account): void
    {
        $account->syncOrdersByCreatedTimeRange(
            Carbon::now()->subMonths(12),
            Carbon::now()
        );
    }
}