<?php

namespace App\Exceptions;

use Throwable;

class UnableFetchAmazonItemUsingMarketingApiException extends \Exception
{
    /**
     * @var string
     */
    protected $asin;

    /**
     * @var Throwable
     */
    protected $previous;

    /**
     * UnableFetchAmazonItemUsingMarketingApiException constructor.
     *
     * @param string     $asin
     * @param Throwable $previous
     */
    public function __construct($asin, Throwable $previous)
    {
        $this->asin = $asin;
        $this->previous = $previous;
    }

    /**
     * @return string
     */
    public function getAsin(): string
    {
        return $this->asin;
    }
}