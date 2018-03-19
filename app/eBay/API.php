<?php

namespace App\eBay;

abstract class API
{
    protected $cache;

    protected $shouldCache = [];

    abstract protected function api();

    abstract public function __call($method, $arguments);

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
}