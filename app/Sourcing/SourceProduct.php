<?php

namespace App\Sourcing;

interface SourceProduct
{

    public function fetch();

    public function getProductId();
}
