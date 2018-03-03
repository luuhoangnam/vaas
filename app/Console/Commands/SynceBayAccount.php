<?php

namespace App\Console\Commands;

use App\Account;
use App\Exceptions\ItemExistedException;
use App\Exceptions\TradingApiException;
use App\Item;
use App\Order;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Enums\DetailLevelCodeType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\OrderType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SynceBayAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ebay:sync {username} {--only_orders} {--only_items}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync All eBay Items & Orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws TradingApiException
     */
    public function handle()
    {
        $username = $this->argument('username');

        $account = Account::find($username);

        // 1. Sync Items
        if ($this->option('only_items')) {
            $this->syncItems($account);

            return 0;
        }

        // 2. Sync Orders
        if ($this->option('only_orders')) {
            $this->syncOrders($account);

            return 0;
        }

        $this->syncAll($account);

        return 0;
    }

    protected function syncAll(Account $account): void
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

    private function syncOrders(Account $account): void
    {
        $account->syncOrdersByCreatedTimeRange(
            Carbon::now()->subMonths(12),
            Carbon::now()
        );
    }
}
