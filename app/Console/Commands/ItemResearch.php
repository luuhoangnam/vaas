<?php

namespace App\Console\Commands;

use App\Account;
use App\Exceptions\TradingApiException;
use DTS\eBaySDK\Finding\Enums\ItemFilterType;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\FindItemsAdvancedRequest;
use DTS\eBaySDK\Finding\Types\FindItemsByProductRequest;
use DTS\eBaySDK\Finding\Types\ItemFilter;
use DTS\eBaySDK\Finding\Types\PaginationInput;
use DTS\eBaySDK\Finding\Types\ProductId;
use DTS\eBaySDK\Finding\Types\SearchItem;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsRequestType;
use Illuminate\Console\Command;

class ItemResearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ebay:research {--query=}  
                                          {--product_id=} 
                                          {--product_id_type=UPC} 
                                          {--seller=*} 
                                          {--date_range=30}
                                          {--location=*}
                                          {--buyer_postal_code=10001}
                                          {--min_feedback_score=}
                                          {--max_feedback_score=}
                                          {--limit=200}
                                          {--csv=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Research';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option('product_id')) {
            $request = new FindItemsByProductRequest;
        } else {
            $request = new FindItemsAdvancedRequest;
        }

        $request->outputSelector = [
            OutputSelectorType::C_SELLER_INFO,
        ];

        if ($this->option('seller')) {
            $request->itemFilter[] = $this->filterItem(
                ItemFilterType::C_SELLER,
                $this->option('seller')
            );
        }

        // Fixed Price Only
        $request->itemFilter[] = $this->filterItem(ItemFilterType::C_LISTING_TYPE, ['FixedPrice']);

        // NEW Item Only
        $request->itemFilter[] = $this->filterItem(ItemFilterType::C_CONDITION, ['New']);

        // Feedback Score
        if ($this->option('min_feedback_score')) {
            $request->itemFilter[] = $this->filterItem(
                ItemFilterType::C_FEEDBACK_SCORE_MIN,
                [$this->option('min_feedback_score')]
            );
        }

        if ($this->option('max_feedback_score')) {
            $request->itemFilter[] = $this->filterItem(
                ItemFilterType::C_FEEDBACK_SCORE_MAX,
                [$this->option('max_feedback_score')]
            );
        }

        // Item Location
        if ($this->option('location')) {
            $request->itemFilter[] = $this->filterItem(
                ItemFilterType::C_LOCATED_IN,
                $this->option('location')
            );
        }

        $request->buyerPostalCode = (string)$this->option('buyer_postal_code');

        $request->paginationInput = new PaginationInput;

        $limit = 100;

        if ($this->option('limit') < 100) {
            $limit = (int)$this->option('limit');
        }

        $request->paginationInput->entriesPerPage = $limit;
        $request->paginationInput->pageNumber     = 1;

        if ($this->option('product_id')) {
            $request->productId = new ProductId([
                'value' => '190941000167',
                'type'  => 'UPC',
            ]);

            $response = $this->finding()->findItemsByProduct($request);
        } else {
            $request->keywords = $this->option('query') ?: '';

            $response = $this->finding()->findItemsAdvanced($request);
        }

        if ($response->ack !== 'Success') {
            dd($response->toArray());
            throw new TradingApiException($request, $response);
        }

        $headers = [
            'Item ID',
            'Title',
            'Price',
            'Sold',
            'Seller',
            "Last {$this->option('date_range')} Days",
        ];

        $rows = collect($response->searchResult->item)->map(function (SearchItem $item) {
            list($quantitySold, $soldLastXDays) = $this->countItemSold($item->itemId, $this->option('date_range'));

            return [
                'item_id'                   => $item->itemId,
                'title'                     => $item->title,
                'price'                     => $item->sellingStatus->currentPrice->value,
                'quantity_sold'             => $quantitySold,
                'seller'                    => $item->sellerInfo->sellerUserName,
                'quantity_sold_last_x_days' => $soldLastXDays,
            ];
        });

        $rows = $rows->sortByDesc('quantity_sold_last_x_days');

        $this->table($headers, $rows);
    }

    protected function filterItem(string $name, array $value): ItemFilter
    {
        return new ItemFilter(compact('name', 'value'));
    }

    protected function finding(): FindingService
    {
        return app(FindingService::class);
    }

    protected function trading(): TradingService
    {
        return $this->getAccount()->trading();
    }

    protected function getItemTransactionsRequest(): GetItemTransactionsRequestType
    {
        return new GetItemTransactionsRequestType;
    }

    protected function getAccount(): Account
    {
        return Account::query()->inRandomOrder()->firstOrFail();
    }

    protected function countItemSold($itemID, $lastDays = 30)
    {
        $request = $this->getItemTransactionsRequest();

        $request->ItemID = (string)$itemID;

        $request->NumberOfDays = (int)$lastDays;

        $request->OutputSelector = [
            'Item.SellingStatus.QuantitySold',
            'PaginationResult.TotalNumberOfEntries',
        ];

        $response = $this->trading()->getItemTransactions($request);

        $quantitySold          = $response->Item->SellingStatus->QuantitySold;
        $quantitySoldLastXDays = $response->PaginationResult->TotalNumberOfEntries;

        return [$quantitySold, $quantitySoldLastXDays];
    }
}
