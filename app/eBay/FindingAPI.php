<?php

namespace App\eBay;

use DTS\eBaySDK\Finding\Services\FindingService;

class FindingAPI extends API
{
    protected $shouldCache = [
        '/^find.+$/i',
        '/^get.+$/i',
    ];

    /**
     * @return FindingService
     */
    protected function api()
    {
        return AppPool::sdk(AppPool::balancing('finding'))->createFinding();
    }

    protected function responseClass(string $method): string
    {
        return 'DTS\\eBaySDK\\Finding\\Types\\' . studly_case($method) . 'Response';
    }
}