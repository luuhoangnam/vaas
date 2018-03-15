<?php

namespace App\Exceptions;

class SourceProductClassDoesNotExistsException extends \Exception
{
    protected $className;

    public function __construct($className)
    {
        $this->className = $className;
    }

    public function getClassName()
    {
        return $this->className;
    }

}