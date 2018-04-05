<?php

namespace App\Sourcing;

class Shipping
{
    protected $cost;
    protected $minTime;
    protected $maxTime;
    protected $service;

    public function __construct($cost = 0.0, $minTime = 1, $maxTime = 5, $service = 'Standard Shipping')
    {
        $this->cost = (float)$cost;
        $this->minTime = $minTime;
        $this->maxTime = $maxTime;
        $this->service = $service;
    }

    public function getCost()
    {
        return $this->cost ?: 0;
    }

    public function __get($name)
    {
        $method = 'get' . studly_case($name);

        if (method_exists($this, $method)) {
            return $this->{$method};
        }

        return $this->{$name};
    }
}