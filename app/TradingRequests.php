<?php

namespace App;

use DTS\eBaySDK\Trading\Types\AbstractRequestType;
use DTS\eBaySDK\Trading\Types\EndItemsRequestType;
use DTS\eBaySDK\Trading\Types\GetCategoryFeaturesRequestType;
use DTS\eBaySDK\Trading\Types\GetItemRequestType;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsRequestType;
use DTS\eBaySDK\Trading\Types\GetNotificationPreferencesRequestType;
use DTS\eBaySDK\Trading\Types\GetOrdersRequestType;
use DTS\eBaySDK\Trading\Types\GetSellerListRequestType;
use DTS\eBaySDK\Trading\Types\GetSuggestedCategoriesRequestType;
use DTS\eBaySDK\Trading\Types\GetUserPreferencesRequestType;
use DTS\eBaySDK\Trading\Types\ReviseItemRequestType;
use DTS\eBaySDK\Trading\Types\SetNotificationPreferencesRequestType;
use DTS\eBaySDK\Trading\Types\VerifyAddItemRequestType;

/**
 * Trait TradingRequests
 *
 * @method prepareAuthRequiredRequest(AbstractRequestType $request): AbstractRequestType
 *
 * @package App
 */
trait TradingRequests
{
    public function endItemsRequest(): EndItemsRequestType
    {
        return $this->prepareAuthRequiredRequest(new EndItemsRequestType);
    }

    public function getSellerListRequest(): GetSellerListRequestType
    {
        return $this->prepareAuthRequiredRequest(new GetSellerListRequestType);
    }

    public function getNotificationPreferencesRequest(): GetNotificationPreferencesRequestType
    {
        return $this->prepareAuthRequiredRequest(new GetNotificationPreferencesRequestType);
    }

    public function getItemTransactionsRequest(): GetItemTransactionsRequestType
    {
        return $this->prepareAuthRequiredRequest(new GetItemTransactionsRequestType);
    }

    public function setNotificationPreferencesRequest(): SetNotificationPreferencesRequestType
    {
        return $this->prepareAuthRequiredRequest(new SetNotificationPreferencesRequestType);
    }

    public function getItemRequest(): GetItemRequestType
    {
        return $this->prepareAuthRequiredRequest(new GetItemRequestType);
    }

    public function reviseItemRequest(): ReviseItemRequestType
    {
        return $this->prepareAuthRequiredRequest(new ReviseItemRequestType);
    }

    public function addItemRequest(): VerifyAddItemRequestType
    {
        return $this->prepareAuthRequiredRequest(new VerifyAddItemRequestType);
    }

    public function getSuggestedCategoriesRequest(): GetSuggestedCategoriesRequestType
    {
        return $this->prepareAuthRequiredRequest(new GetSuggestedCategoriesRequestType);
    }

    public function getUserPreferencesRequest(): GetUserPreferencesRequestType
    {
        return $this->prepareAuthRequiredRequest(new GetUserPreferencesRequestType);
    }

    public function getCategoryFeaturesRequest(): GetCategoryFeaturesRequestType
    {
        return $this->prepareAuthRequiredRequest(new GetCategoryFeaturesRequestType);
    }

    public function getOrdersRequest(): GetOrdersRequestType
    {
        return $this->prepareAuthRequiredRequest(new GetOrdersRequestType);
    }
}