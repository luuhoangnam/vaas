<?php

namespace App\Researching;

use App\Exceptions\ApiException;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\ItemFilterType;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\FindItemsAdvancedRequest;
use DTS\eBaySDK\Finding\Types\ItemFilter;
use DTS\eBaySDK\Finding\Types\PaginationInput;
use DTS\eBaySDK\Finding\Types\SearchItem;
use Illuminate\Support\Collection;

class CompetitorResearch
{
    protected $username;
    protected $dateRange;
    protected $buyerPostalCode;

    private $performance; // Cached Value
    private $items; // Cached Value

    public function __construct($username, $dateRange, $buyerPostalCode = 10001)
    {
        $this->username        = $username;
        $this->dateRange       = $dateRange;
        $this->buyerPostalCode = $buyerPostalCode;
    }

    public function performance()
    {
        if ($this->performance) {
            return $this->performance;
        }

        list ($performance, $items) = $this->research();

        return $this->performance = $performance;
    }

    public function items()
    {
        if ($this->items) {
            return $this->items;
        }

        list ($performance, $items) = $this->research();

        return $this->items = $items;
    }

    protected function finding(): FindingService
    {
        return app(FindingService::class);
    }

    protected function research()
    {
        $activeListings = $this->getActiveListing();

        $items = $this->getItems();

        $performance = [
            'sell_through'    => '',
            'active_listings' => $activeListings,
            'sold_items'      => '',
            'sale_earning'    => '',
        ];

        return [
            $performance,
            $items,
        ];
    }

    protected function getActiveListing(): int
    {
        $request = new FindItemsAdvancedRequest;

        $request->itemFilter[] = $this->sellerFilter();

        $request->buyerPostalCode = (string)$this->buyerPostalCode;

        $request->paginationInput = new PaginationInput;

        $request->paginationInput->entriesPerPage = 1;
        $request->paginationInput->pageNumber     = 1;

        $response = $this->finding()->findItemsAdvanced($request);

        if ($response->ack === AckValue::C_FAILURE) {
            throw new ApiException($request, $response);
        }

        return $response->paginationOutput->totalEntries;
    }

    protected function getItems(): Collection
    {
        $cacheKey = md5(serialize([
            'username'   => $this->username,
            'date_range' => $this->dateRange,
        ]));

        $cacheTime = 60 * 24; // 1 Day

        return cache()->remember($cacheKey, $cacheTime, function () {
            $request = new FindItemsAdvancedRequest;

            $request->itemFilter[] = $this->sellerFilter();

            $request->buyerPostalCode = (string)$this->buyerPostalCode;

            $request->paginationInput = new PaginationInput;

            $request->paginationInput->entriesPerPage = 100;
            $request->paginationInput->pageNumber     = 1;

            $items = new Collection;

            do {
                $response = $this->finding()->findItemsAdvanced($request);

                if ($response->ack === AckValue::C_FAILURE) {
                    throw new ApiException($request, $response);
                }

                /** @noinspection PhpParamsInspection */
                $items = $items->concat($response->searchResult->item);

                $request->paginationInput->pageNumber += 1;

                $next = $response->paginationOutput->totalPages > 0 && $response->paginationOutput->pageNumber < $response->paginationOutput->totalPages;
            } while ($next);

            return $items;
        });
    }

    protected function sellerFilter(): ItemFilter
    {
        return new ItemFilter(['name' => ItemFilterType::C_SELLER, 'value' => [$this->username]]);
    }
}