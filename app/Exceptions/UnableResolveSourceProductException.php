<?php

namespace App\Exceptions;

use App\Item;
use Throwable;

class UnableResolveSourceProductException extends \Exception
{
    /**
     * @var Item
     */
    protected $item;

    /**
     * @var null|Throwable
     */
    protected $previous;

    /**
     * UnableResolveSourceProductException constructor.
     *
     * @param Item           $item
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(Item $item, $code = 0, Throwable $previous = null)
    {
        $this->item = $item;
        $this->code = $code;
        $this->previous = $previous;
    }

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }
}