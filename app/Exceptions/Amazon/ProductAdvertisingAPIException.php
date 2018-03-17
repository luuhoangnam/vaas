<?php

namespace App\Exceptions\Amazon;

class ProductAdvertisingAPIException extends \Exception
{
    const ITEM_NOT_ACCESSIBLE = 'AWS.ECommerceService.ItemNotAccessible';
    const INVALID_PARAMETER_VALUE = 'AWS.InvalidParameterValue';

    public function __construct($message, $code, \Throwable $previous = null)
    {
        $this->message = $message;
        $this->code    = $code;

        parent::__construct('', 0, $previous);
    }
}