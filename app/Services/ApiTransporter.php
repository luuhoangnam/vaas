<?php

namespace App\Services;

use DTS\eBaySDK\Services\BaseService;
use DTS\eBaySDK\Types\BaseType;

class ApiTransporter
{
    /**
     * @var BaseService
     */
    protected $service;

    protected $cachedRequests = [
        // FindingService
        'findItemsByKeywords'
    ];

    /**
     * ApiTransporter constructor.
     *
     * @param BaseService $service
     */
    public function __construct(BaseService $service)
    {
        $this->service = $service;
    }

    public function __call($name, $arguments)
    {
        if (!in_array($name, $this->cachedRequests)) {
            call_user_func_array([$this->service, $name], $arguments);
        }

        /** @var BaseType $request */
        $request = $arguments[0];

        $cacheKey = $this->getRequestSignature($request);

        return cache()->remember($cacheKey, 60, function () use ($name, $arguments) {
            return call_user_func_array([$this->service, $name], $arguments);
        });
    }

    protected function getRequestSignature(BaseType $request)
    {
        return md5(serialize($request->toArray()));
    }
}