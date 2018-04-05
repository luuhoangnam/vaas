<?php

namespace App\Sourcing\Exceptions;

use Throwable;

class AmazonAPIException extends \Exception
{
    protected $code;

    public function __construct(string $message = "", string $code = "", Throwable $previous = null)
    {
        $this->code = $code;

        parent::__construct($message, 0, $previous);
    }
}