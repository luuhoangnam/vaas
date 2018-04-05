<?php

namespace App\Sourcing\Suppliers\Amazon;

use App\Sourcing\Exceptions\AmazonAPIException;

interface Client
{
    /**
     * @param mixed  $id
     * @param string $mode
     *
     * @return array
     * @throws AmazonAPIException
     */
    public function get($id, $mode);
}