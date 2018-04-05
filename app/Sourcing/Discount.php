<?php

namespace App\Sourcing;

class Discount
{
    protected $amount;
    protected $type;

    public function __construct($amount, $type = 'fixed')
    {
        $this->amount = $amount;
        $this->type = $type;
    }

    public function getAmount()
    {
        return $this->amount ?: 0;
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