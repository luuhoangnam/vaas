<?php
/**
 * Created by PhpStorm.
 * User: luuhoangnam
 * Date: 2/26/18
 * Time: 8:08 AM
 */

namespace App\Exceptions;


class ItemExistedException extends \Exception
{
    protected $item;

    /**
     * ItemExistedException constructor.
     *
     * @param $item
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }
}