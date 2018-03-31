<?php

namespace App\Jobs;

use App\eBay\AppPool;
use App\eBay\FindingAPI;
use App\eBay\ItemCondition;
use App\eBay\ItemLocation;
use App\Exceptions\FindingApiException;
use App\Spy\Competitor;
use DTS\eBaySDK\BulkDataExchange\Enums\ListingType;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\ItemFilterType;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\Condition;
use DTS\eBaySDK\Finding\Types\FindItemsByKeywordsRequest;
use DTS\eBaySDK\Finding\Types\ItemFilter;
use DTS\eBaySDK\Finding\Types\PaginationInput;
use DTS\eBaySDK\Finding\Types\SearchItem;
use DTS\eBaySDK\Trading\Enums\ListingTypeCodeType;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FindCompetitorsByKeyword implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $keyword;
    protected $condition;
    protected $limit;
    protected $location;
    /**
     * @var string
     */
    protected $listingType;
    /**
     * @var null
     */
    protected $feedbackScoreMin;
    /**
     * @var null
     */
    protected $feedbackScoreMax;
    /**
     * @var null
     */
    protected $minPrice;
    /**
     * @var null
     */
    protected $maxPrice;

    public function __construct(
        $keyword,
        $condition = ItemCondition::NEW,
        $location = ItemLocation::US,
        $listingType = 'FixedPrice',
        $minPrice = null,
        $maxPrice = null,
        $feedbackScoreMin = null,
        $feedbackScoreMax = null,
        $limit = 100
    ) {
        $this->keyword          = $keyword;
        $this->location         = $location;
        $this->condition        = $condition;
        $this->limit            = $limit;
        $this->listingType      = $listingType;
        $this->feedbackScoreMin = $feedbackScoreMin;
        $this->feedbackScoreMax = $feedbackScoreMax;
        $this->minPrice         = $minPrice;
        $this->maxPrice         = $maxPrice;
    }

    public function handle()
    {
        $request = new FindItemsByKeywordsRequest;

        $request->keywords = $this->keyword;

        $request->paginationInput = new PaginationInput (['pageNumber' => 1, 'entriesPerPage' => 100]);

        $request->outputSelector  = [OutputSelectorType::C_SELLER_INFO];
        $request->buyerPostalCode = (string)10001;

        $request->itemFilter = $this->filters();

        do {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $response = $this->finding()->findItemsByKeywords($request, 24 * 60);

            if ($response->ack === AckValue::C_FAILURE) {
                throw new FindingApiException($request, $response);
            }

            collect($response->searchResult->item)
                ->map(function (SearchItem $item) {
                    return $item->sellerInfo->sellerUserName;
                })
                ->filter(function ($competitor) {
                    return ! in_array(
                        $competitor,
                        config('ebay.miner.competitor.blacklist')
                    );
                })
                ->filter(function ($competitor) {
                    return ! Competitor::exists($competitor);
                })
                ->each(function ($competitor) {
                    // Research Seller
                    dd($competitor);

                    dd();
                });

            $paginationOutput = $response->paginationOutput;
        } while ($paginationOutput->pageNumber < $paginationOutput->totalPages);

        dd();
    }

    protected function filters(): array
    {
        $filters[] = $this->itemFilter(ItemFilterType::C_LISTING_TYPE, $this->listingType);
        $filters[] = $this->itemFilter(ItemFilterType::C_CONDITION, $this->condition);
        $filters[] = $this->itemFilter(ItemFilterType::C_LOCATED_IN, $this->location);

        if ($this->feedbackScoreMin) {
            $filters[] = $this->itemFilter(ItemFilterType::C_FEEDBACK_SCORE_MIN, $this->feedbackScoreMin);
        }

        if ($this->feedbackScoreMax) {
            $filters[] = $this->itemFilter(ItemFilterType::C_FEEDBACK_SCORE_MAX, $this->feedbackScoreMax);
        }

        if ($this->minPrice) {
            $filters[] = $this->itemFilter(ItemFilterType::C_MIN_PRICE, $this->minPrice);
        }

        if ($this->maxPrice) {
            $filters[] = $this->itemFilter(ItemFilterType::C_MAX_PRICE, $this->maxPrice);
        }

        return $filters;
    }

    protected function itemFilter($name, $value): ItemFilter
    {
        return new ItemFilter(['name' => $name, 'value' => is_array($value) ? (string)$value : [(string)$value]]);
    }

    /**
     * @return FindingAPI|FindingService
     */
    protected function finding()
    {
        return new FindingAPI;
    }
}
