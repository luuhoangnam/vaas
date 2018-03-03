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
        $request = $account->getSellerListRequest();

        # START TIME RAGE WITHIN LAST 3 MONTHS
        $request->StartTimeFrom = dt(Carbon::now()->subMonths(3));
        $request->StartTimeTo   = dt(Carbon::now());

        # PAGINATION
        $request->Pagination = new PaginationType;

        $request->Pagination->EntriesPerPage = 100;
        $request->Pagination->PageNumber     = 1;

        # OUTPUT SELECTOR
        $request->DetailLevel = [DetailLevelCodeType::C_RETURN_ALL];

        $request->OutputSelector = [
            'ItemArray.Item.ItemID',
            'ItemArray.Item.Title',
            'ItemArray.Item.ListingDetails.StartTime',
            'ItemArray.Item.SKU',
            'ItemArray.Item.Quantity',
            'ItemArray.Item.ProductListingDetails.UPC',
            'ItemArray.Item.PrimaryCategory.CategoryID',
            'ItemArray.Item.SellingStatus.QuantitySold',
            'ItemArray.Item.SellingStatus.CurrentPrice',
            'ItemArray.Item.SellingStatus.ListingStatus',
            // Pagination
            'PaginationResult',
            'HasMoreItems',
        ];

        $items = new Collection;

        $this->info('Fetching Items from eBay');

        $bar = $this->output->createProgressBar();

        do {
            $response = $account->trading()->getSellerList($request);

            if ($response->Ack !== 'Success') {
                throw new TradingApiException($request, $response);
            }

            $items = $items->concat(collect($response->ItemArray->Item));

            $bar->setBarWidth($response->PaginationResult->TotalNumberOfPages);
            $bar->advance();

            # UPDATE PAGINATION PAGE NUMBER
            $request->Pagination->PageNumber++;
        } while ($response->HasMoreItems);

        $bar->finish();

        $items = $items->map(function (ItemType $item) use ($account) : Item {

            $attrs = Item::extractItemAttributes($item, [
                'item_id',
                'title',
                'price',
                'quantity',
                'quantity_sold',
                'primary_category_id',
                'start_time',
                'status',
                //
                'sku',
                'upc',
            ]);

            try {
                return $account->saveItem($attrs);
            } catch (ItemExistedException $exception) {
                $itemModel = Item::find($item->ItemID);

                $itemModel->update($attrs);

                return $itemModel;
            }
        });

        $this->line("");
        $this->info("Found {$items->count()} items and synced with database.");
        $this->info("Current Active: {$account->activeItems()->count()}. Completed: {$account->completedItems()->count()}");
    }

    private function syncOrders(Account $account): void
    {
        $account->syncOrdersByTimeRange(
            Carbon::now()->subMonths(12),
            Carbon::now()
        );
    }
}
