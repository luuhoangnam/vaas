<?php

namespace App\Cashback;

use App\Exceptions\InvalidAmazonAssociatesItemException;
use App\Exceptions\NonAffiliatableException;
use Revolution\Amazon\ProductAdvertising\AmazonClient;

class AmazonAssociates implements CashbackProgram
{
    public function link($asin)
    {
        return route('redirect.amazon', $asin);
    }

    public function getAssociateLink($asin)
    {
        $response = $this->queryItem($asin);

        return $response["Items"]["Item"]["DetailPageURL"];
    }

    protected function queryItem($asin): array
    {
        $response = $this->amazon()->item($asin);

        if ($this->hasError($response, 'AWS.InvalidParameterValue')) {
            throw new InvalidAmazonAssociatesItemException($asin);
        }

        if ($this->hasError($response, 'AWS.ECommerceService.ItemNotAccessible')) {
            throw new NonAffiliatableException($asin);
        }

        return $response;
    }

    protected function hasError($response, $code = null): bool
    {
        if ($code) {
            return @$response['Items']['Request']['Errors']['Error']['Code'] == $code;
        }

        return (bool)@$response['Items']['Request']['Errors']['Error'];
    }

    protected function amazon(): AmazonClient
    {
        return app(AmazonClient::class);
    }
}