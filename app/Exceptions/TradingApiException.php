<?php
/**
 * Created by PhpStorm.
 * User: luuhoangnam
 * Date: 2/26/18
 * Time: 12:26 AM
 */

namespace App\Exceptions;


class TradingApiException extends \Exception
{
    protected $request;

    protected $response;

    public function __construct($request, $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}