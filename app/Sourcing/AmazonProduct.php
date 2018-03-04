<?php

namespace App\Sourcing;

use App\Exceptions\CanNotFetchProductInformation;

class AmazonProduct implements SourceProduct
{
    protected $asin;

    public function __construct($asin)
    {
        $this->asin = $asin;
    }

    public function getProductId(): string
    {
        return $this->asin;
    }

    public function fetch(): array
    {
        // Using Marketing API Strategy
        $strategies = config('ebay.sourcing.amazon.strategies');

        foreach ($strategies as $strategyClass) {
            try {
                /** @var FetchingStrategy $strategy */
                $strategy = new $strategyClass($this);

                return $strategy->fetch();
            } catch (\Exception $exception) {
                // Can't use this strategy to fetch product information
                continue;
            }
        }

        throw new CanNotFetchProductInformation($this);
    }
}