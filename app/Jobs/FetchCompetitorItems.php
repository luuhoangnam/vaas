<?php

namespace App\Jobs;

use App\eBay\FindingAPI;
use App\Exceptions\FindingApiException;
use App\Miner\Competitor;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\ItemFilterType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\FindItemsAdvancedRequest;
use DTS\eBaySDK\Finding\Types\FindItemsAdvancedResponse;
use DTS\eBaySDK\Finding\Types\PaginationInput;
use DTS\eBaySDK\Finding\Types\SearchItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FetchCompetitorItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $competitor;
    protected $page;

    public function __construct(Competitor $competitor, $page = 1)
    {
        $this->competitor = $competitor;
        $this->page       = $page;
    }

    /**
     * @throws FindingApiException
     */
    public function handle()
    {
        $request = new FindItemsAdvancedRequest;

        $request->itemFilter[] = item_filter(ItemFilterType::C_SELLER, $this->competitor->username);

        $request->paginationInput = new PaginationInput;

        $request->paginationInput->entriesPerPage = 100;
        $request->paginationInput->pageNumber     = $this->page;

        $finding = $this->finding();

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $response = $finding->findItemsAdvanced($request, 24 * 60);

        if ($response->ack === AckValue::C_FAILURE) {
            throw new FindingApiException($request, $response);
        }

        $this->items($response)->each(function (SearchItem $item) {
            $this->competitor->persistSearchItem($item);
        });

        // If we want to stop the loop when we have enough items, place it in the condition right below
        $this->rescursive($response);
    }

    /**
     * @return FindingAPI|FindingService
     */
    protected function finding()
    {
        return new FindingAPI;
    }

    protected function items(FindItemsAdvancedResponse $response)
    {
        return new Collection($response->searchResult->item);
    }

    protected function hasMorePage(FindItemsAdvancedResponse $response): bool
    {
        $output = $response->paginationOutput;

        return $output->pageNumber < $output->totalPages;
    }

    protected function rescursive(FindItemsAdvancedResponse $response): void
    {
        $pagination = $response->paginationOutput;

        if ($pagination->pageNumber === 1 && $this->hasMorePage($response)) {
            $totalPages = $pagination->totalPages;
            $limit      = config('miner.search.page_limit', 100);
            $pageLimit  = $limit;
            $totalPages = $totalPages > $pageLimit ? $pageLimit : $totalPages;

            $veryNextPage = $pagination->pageNumber + 1;
            $followPages  = range($veryNextPage, $totalPages);

            foreach ($followPages as $page) {
                self::dispatch($this->competitor, $page); // <- Queue the next page
            }
        }
    }
}
