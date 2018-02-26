<?php

namespace App\Console\Commands\Ebay;

use App\Account;
use App\Exceptions\ItemExistedException;
use App\Exceptions\TradingApiException;
use App\Item;
use DTS\eBaySDK\Trading\Enums\DetailLevelCodeType;
use DTS\eBaySDK\Trading\Types\GetSellerListRequestType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SyncItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ebay:sync:items {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync All eBay Items';

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

        $account = $this->getAccount($username);

        $request = $account->getSellerListRequest();

        # START TIME RAGE WITHIN LAST 3 MONTHS
        $request->StartTimeFrom = new \DateTime(Carbon::now()->subMonths(3));
        $request->StartTimeTo   = new \DateTime(Carbon::now());

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

            $newItems = $response->toArray()['ItemArray']['Item'];

            $items = $items->concat($newItems);

            $bar->setBarWidth($response->PaginationResult->TotalNumberOfPages);
            $bar->advance();

            # UPDATE PAGINATION PAGE NUMBER
            $request->Pagination->PageNumber++;
        } while ($response->HasMoreItems);

        $bar->finish();

        $items = $items->map(function (array $item) use ($account) : Item {
            try {
                return $account->saveItem([
                    'item_id'             => $item['ItemID'],
                    'title'               => $item['Title'],
                    'price'               => $item['SellingStatus']['CurrentPrice']['value'],
                    'quantity'            => $item['Quantity'],
                    'quantity_sold'       => $item['SellingStatus']['QuantitySold'],
                    'primary_category_id' => $item['PrimaryCategory']['CategoryID'],
                    'start_time'          => new Carbon($item['ListingDetails']['StartTime']),
                    'status'              => $item['SellingStatus']['ListingStatus'],
                ]);
            } catch (ItemExistedException $exception) {
                return Item::find($item['ItemID']);
            }
        });

        $this->line("");
        $this->info("Found {$items->count()} items and synced with database.");
        $this->info("Current Active: {$account->activeItems()->count()}. Completed: {$account->completedItems()->count()}");
    }

    protected function getAccount($username): Account
    {
        return Account::query()->where('username', $username)->firstOrFail();
    }
}
