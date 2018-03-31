<?php

namespace App\Jobs;

use App\eBay\FindingAPI;
use App\Exceptions\FindingApiException;
use App\Spy\Competitor;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\ItemFilterType;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
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

class FindCompetitorItemTask implements ShouldQueue
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

        $request->outputSelector = [
            OutputSelectorType::C_UNIT_PRICE_INFO,
        ];

        $finding = $this->finding();

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $response = $finding->findItemsAdvanced($request, 24 * 60);

        if ($response->ack === AckValue::C_FAILURE) {
            throw new FindingApiException($request, $response);
        }

        $this->items($response)->each(function (SearchItem $item) {
            $this->competitor->persistItem($item);
        });

        // If we want to stop the loop when we have enough items, place it in the condition right below
        if ($this->page === 1 && $this->hasMorePage($response)) {
            $maxPage = $response->paginationOutput->totalPages;
            $maxPage = $maxPage < 100 ? $maxPage : 100;

            foreach (range(2, $maxPage) as $page) {
                FindCompetitorItemTask::dispatch($this->competitor, $page); // <- Queue the next page
            }
        }
    }

    /**
     * @return FindingAPI|FindingService
     */
    protected function finding()
    {
        return new FindingAPI;
    }

    protected function items(FindItemsAdvancedResponse $response): Collection
    {
        return new Collection($response->searchResult->item);
    }

    protected function hasMorePage(FindItemsAdvancedResponse $response): bool
    {
        $paginationOutput = $response->paginationOutput;

        return $paginationOutput->pageNumber < $paginationOutput->totalPages;
    }
}
