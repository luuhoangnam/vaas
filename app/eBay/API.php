<?php

namespace App\eBay;

use DTS\eBaySDK\Types\BaseType;
use Illuminate\Contracts\Cache\Factory as Cache;

abstract class API
{
    protected $cache;

    protected $shouldCache = [];

    public function __construct(Cache $cache = null)
    {
        $this->cache = $cache ?: app(Cache::class);
    }

    abstract protected function api();

    abstract protected function responseClass(string $method): string;

    public function __call($method, $arguments)
    {
        if ( ! $arguments[0] instanceof BaseType) {
            return forward_static_call_array([$this->api(), $method], $arguments);
        }

        $request = method_exists($this, 'prepare') ? $this->prepare($arguments[0]) : $arguments[0];

        $cacheTime = isset($arguments[1]) ? (float)$arguments[1] : $this->defaultCacheTime();

        if ($this->isCached($method)) {
            $cacheKey = $this->cacheKey($request);

            if ( ! $cacheTime) {
                $this->cache->forget($cacheKey);

                $cacheTime = $this->defaultCacheTime();
            }

            $data = $this->cacheData($cacheKey, $cacheTime, $method, $request);

            $responseClass = $this->responseClass($method);

            return new $responseClass($data);
        }

        return $this->forward($method, $request);
    }

    protected function forward($method, $request)
    {
        return $this->api()->{$method}($request);
    }

    protected function isCached($method): bool
    {
        foreach ($this->shouldCache as $pattern) {
            if ($pattern == $method) {
                return true;
            }

            if (preg_match($pattern, $method)) {
                return true;
            }
        }

        return false;
    }

    protected function cacheKey(BaseType $request): string
    {
        return md5(serialize($request->toArray()));
    }

    protected function cacheData(string $cacheKey, $cacheTime, string $method, BaseType $request)
    {
        return cache()->remember($cacheKey, $cacheTime, function () use ($method, $request) {
            /** @var BaseType $response */
            $response = $this->forward($method, $request);

            return $response->toArray();
        });
    }

    protected function defaultCacheTime()
    {
        $global   = config('ebay.api.cache_time');
        $specific = config('ebay.api.' . get_class($this) . '.cache_time');

        return $specific ?: $global;
    }
}