<?php

namespace App\Jobs;

use App\eBay\FindingAPI;
use App\eBay\Helpers\FindItemsAdvancedResolver;
use App\Exceptions\FindingApiException;
use App\Spy\CompetitorItem;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\ItemFilterType;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\FindItemsAdvancedRequest;
use DTS\eBaySDK\Finding\Types\ItemFilter;
use DTS\eBaySDK\Finding\Types\PaginationInput;
use DTS\eBaySDK\Finding\Types\SearchItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FindItemAdvanced implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $username;
    protected $page;

    public function __construct($username, $page = 1)
    {
        $this->username = $username;
        $this->page     = $page;
    }

    public function handle()
    {
        $request = new FindItemsAdvancedRequest;

        $request->itemFilter[] = new ItemFilter(['name' => ItemFilterType::C_SELLER, 'value' => [$this->username]]);

        $request->paginationInput = new PaginationInput;

        $request->paginationInput->entriesPerPage = 100;
        $request->paginationInput->pageNumber     = $this->page;

        $request->outputSelector = [
            OutputSelectorType::C_UNIT_PRICE_INFO,
        ];

        $response = $this->finding()->findItemsAdvanced($request);

        if ($response->ack === AckValue::C_FAILURE) {
            throw new FindingApiException($request, $response);
        }

        FindItemsAdvancedResolver::items($response)->each(function (SearchItem $item) {
            CompetitorItem::persist($this->username, $item);
        });

        // If we want to stop the loop when we have enough items, place it in the condition right below
        if (FindItemsAdvancedResolver::hasMorePage($response)) {
            self::dispatchNow($this->username, $this->page++); // <- Queue the next page
        }
    }

    /**
     * @return FindingAPI|FindingService
     */
    public function finding()
    {
        return new FindingAPI;
    }
}
