<?php

namespace App\eBay\Helpers;

use App\Exceptions\FindingApiException;
use DTS\eBaySDK\Finding\Types\FindItemsAdvancedResponse;
use Illuminate\Support\Collection;

class FindItemsAdvancedResolver extends FindingAPIResolver
{
    /**
     * @param FindItemsAdvancedResponse $response
     *
     * @return Collection
     */
    public static function items($response): Collection
    {
        return new Collection($response->searchResult->item);
    }
}