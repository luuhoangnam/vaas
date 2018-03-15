<?php

namespace App\Sourcing;

interface FetchingStrategy
{
    public function fetch();

    public function rawResult();
}