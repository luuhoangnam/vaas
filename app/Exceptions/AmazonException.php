<?php

namespace App\Exceptions;

class AmazonException extends \Exception
{
    public function __construct($message, $code)
    {
        $this->message = $message;
        $this->code = $code;
    }
}
