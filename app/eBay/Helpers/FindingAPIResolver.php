<?php

namespace App\eBay\Helpers;

use App\Exceptions\FindingApiException;
use DTS\eBaySDK\Finding\Types\BaseFindingServiceResponse;
use Illuminate\Support\Collection;

abstract class FindingAPIResolver
{
    abstract public static function items($response): Collection;

    /**
     * @param BaseFindingServiceResponse $response
     *
     * @return bool
     */
    public static function hasMorePage($response): bool
    {
        return $response->paginationOutput->totalPages <= $response->paginationOutput->pageNumber;
    }

    public static function exception()
    {
        return FindingApiException::class;
    }
}