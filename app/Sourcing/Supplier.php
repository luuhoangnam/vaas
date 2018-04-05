<?php

namespace App\Sourcing;

interface Supplier
{
    public function get($id): Product;

    public function lookup($term, $mode): Product;

    public function otherFees($price);
}