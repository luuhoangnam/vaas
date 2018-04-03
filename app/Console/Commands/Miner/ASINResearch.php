<?php

namespace App\Console\Commands\Miner;

use App\eBay\FindingAPI;
use App\Exceptions\Amazon\ProductAdvertisingAPIException;
use App\Exceptions\FindingApiException;
use App\Miner\Competitor;
use App\Sourcing\AmazonAPI;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\ItemFilterType;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\FindItemsByKeywordsRequest;
use DTS\eBaySDK\Finding\Types\FindItemsByKeywordsResponse;
use DTS\eBaySDK\Finding\Types\PaginationInput;
use DTS\eBaySDK\Finding\Types\SearchItem;
use Illuminate\Console\Command;

class ASINResearch extends Command
{
    protected $signature = 'research:asin {asin}';

    protected $description = 'Product Research by ASIN (inspired by Zik)';

    /**
     * @throws ProductAdvertisingAPIException
     * @throws FindingApiException
     */
    public function handle()
    {
        $asin = $this->argument('asin');

        #1. Get UPC if No UPC, void
        $product = AmazonAPI::inspect($asin);

        if ( ! $upc = $product['attributes']['UPC']) {
            $this->error('Can not get UPC');

            return;
        }

        $response = $this->search($product);

        $items = collect($response->searchResult->item);
        $this->info("Found {$items->count()} Items");

        $items = $items->unique(function (SearchItem $item) {
            return $item->sellerInfo->sellerUserName;
        });
        $this->info("With {$items->count()} Unique Seller");

        $items = $items->filter(function (SearchItem $item) {
            return Competitor::notExists($item->sellerInfo->sellerUserName);
        });
        $this->info("And {$items->count()} Seller Does Not Researched Yet");

        $sellers = $items->map(function (SearchItem $item) {
            return $item->sellerInfo->sellerUserName;
        });

        $sellers->each(function ($username) {
            try {
                Competitor::add($username);
                $this->info("{$username}: Success");
            } catch (\InvalidArgumentException $exception) {
                $this->error("{$username}: Invalid");
            }
        });

        $this->info('Done!');
    }

    /**
     * @return FindingService|FindingAPI
     */
    protected function finding()
    {
        return new FindingAPI;
    }

    /**
     * @param $product
     *
     * @return FindItemsByKeywordsResponse
     * @throws FindingApiException
     */
    protected function search($product)
    {
        $request = new FindItemsByKeywordsRequest;

        $request->keywords = (string)$product['attributes']['UPC'];

        $request->paginationInput                 = new PaginationInput;
        $request->paginationInput->pageNumber     = 1;
        $request->paginationInput->entriesPerPage = 100;

        $request->outputSelector = [OutputSelectorType::C_SELLER_INFO];

        # FILTERS
        $minFeedback = config('miner.research.feedback.min');
        $priceRate   = config('miner.research.price_rate');

        $request->itemFilter[] = item_filter(ItemFilterType::C_LOCATED_IN, 'US');
        $request->itemFilter[] = item_filter(ItemFilterType::C_CONDITION, 'New');
        $request->itemFilter[] = item_filter(ItemFilterType::C_FEEDBACK_SCORE_MIN, $minFeedback);
        $request->itemFilter[] = item_filter(ItemFilterType::C_MIN_PRICE, $product['price'] * $priceRate);
        # END FILTERS

        $response = $this->finding()->findItemsByKeywords($request);

        if ($response->ack === AckValue::C_FAILURE) {
            throw new FindingApiException($request, $response);
        }

        return $response;
    }
}
