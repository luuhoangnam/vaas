<?php

namespace App\Sourcing;

class Seller
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function is($name)
    {
        return $this->name == $name;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function noTax()
    {
        return ! $this->tax();
    }

    public function tax()
    {
        $taxedSellers = config('sourcing.taxed_sellers');

        if (in_array($this->name, $taxedSellers)) {
            return true;
        }

        return false;
    }
}