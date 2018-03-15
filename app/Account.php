<?php

namespace App;

use App\Exceptions\TradingApiException;
use App\Ranking\Trackable;
use DTS\eBaySDK\BusinessPoliciesManagement\Services\BusinessPoliciesManagementService;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Enums\CountryCodeType;
use DTS\eBaySDK\Trading\Enums\CurrencyCodeType;
use DTS\eBaySDK\Trading\Enums\DetailLevelCodeType;
use DTS\eBaySDK\Trading\Enums\EnableCodeType;
use DTS\eBaySDK\Trading\Enums\FeatureIDCodeType;
use DTS\eBaySDK\Trading\Enums\ListingDurationCodeType;
use DTS\eBaySDK\Trading\Enums\ListingTypeCodeType;
use DTS\eBaySDK\Trading\Enums\NotificationEventTypeCodeType;
use DTS\eBaySDK\Trading\Enums\SiteCodeType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\AbstractRequestType;
use DTS\eBaySDK\Trading\Types\AmountType;
use DTS\eBaySDK\Trading\Types\ApplicationDeliveryPreferencesType;
use DTS\eBaySDK\Trading\Types\CategoryType;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\NotificationEnableArrayType;
use DTS\eBaySDK\Trading\Types\NotificationEnableType;
use DTS\eBaySDK\Trading\Types\OrderType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use DTS\eBaySDK\Trading\Types\PictureDetailsType;
use DTS\eBaySDK\Trading\Types\ProductListingDetailsType;
use DTS\eBaySDK\Trading\Types\SellerPaymentProfileType;
use DTS\eBaySDK\Trading\Types\SellerProfilesType;
use DTS\eBaySDK\Trading\Types\SellerReturnProfileType;
use DTS\eBaySDK\Trading\Types\SellerShippingProfileType;
use DTS\eBaySDK\Trading\Types\TransactionType;
use DTS\eBaySDK\Trading\Types\VerifyAddItemResponseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Account extends Model
{
    use TradingRequests, Trackable;

    protected $fillable = ['username', 'token'];

    public static function find($username): Account
    {
        return static::query()->where('username', $username)->firstOrFail();
    }

    public static function exists($username): bool
    {
        return static::query()->where('username', $username)->exists();
    }

    public static function random(): Account
    {
        return Account::query()->inRandomOrder()->firstOrFail();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function activeItems()
    {
        return $this->hasMany(Item::class, 'account_id')
                    ->where('status', 'Active');
    }

    public function completedItems(): Account
    {
        return $this->hasMany(Item::class, 'account_id')
                    ->where('status', 'Completed');
    }

    public function unsoldItemsByListingAge($days = null)
    {
        $query = $this->hasMany(Item::class, 'account_id')
                      ->where('status', 'Active')
                      ->where('quantity_sold', 0);

        if (is_int($days)) {
            $query = $query->whereDate('start_time', '<', Carbon::now()->subDays($days));
        }

        return $query;
    }

    public function updateOrCreateOrder(OrderType $order): Order
    {
        return $this->orders()->updateOrCreate(
            ['order_id' => $order->OrderID],
            Order::extractAttribute($order)
        );
    }

    public function trading(): TradingService
    {
        return app(TradingService::class);
    }

    public function businessPoliciesManagement(): BusinessPoliciesManagementService
    {
        return app(BusinessPoliciesManagementService::class);
    }

    public function prepareAuthRequiredRequest(AbstractRequestType $request): AbstractRequestType
    {
        // Credentials
        $credentials                = new CustomSecurityHeaderType;
        $credentials->eBayAuthToken = $this['token'];

        // Prepare the Request
        $request->RequesterCredentials = $credentials;

        return $request;
    }

    public function subscribePlatformNotification(): void
    {
        $request = $this->setNotificationPreferencesRequest();

        $request->ApplicationDeliveryPreferences = new ApplicationDeliveryPreferencesType;

        $request->ApplicationDeliveryPreferences->ApplicationURL = route('ebay.events');

        $request->UserDeliveryPreferenceArray = new NotificationEnableArrayType;

        $request->UserDeliveryPreferenceArray->NotificationEnable = [
            // Account
            $this->enableEvent(NotificationEventTypeCodeType::C_USERID_CHANGED),
            // Item
            $this->enableEvent(NotificationEventTypeCodeType::C_ITEM_CLOSED),
            $this->enableEvent(NotificationEventTypeCodeType::C_ITEM_LISTED),
            $this->enableEvent(NotificationEventTypeCodeType::C_ITEM_REVISED),
            // Order
            $this->enableEvent(NotificationEventTypeCodeType::C_ITEM_MARKED_SHIPPED),
            $this->enableEvent(NotificationEventTypeCodeType::C_ITEM_SOLD),
            $this->enableEvent(NotificationEventTypeCodeType::C_FIXED_PRICE_TRANSACTION),
        ];

        $response = $this->trading()->setNotificationPreferences($request);

        if ($response->Ack !== 'Success') {
            throw new TradingApiException($request, $response);
        }
    }

    public function addItem(array $data): VerifyAddItemResponseType
    {
        $request = $this->addItemRequest();

        $request->Item = new ItemType;

        // Title
        $request->Item->Title = $data['title'];

        // Quantity
        $request->Item->Quantity = array_get($data, 'quantity', 1);

        if (@$data['sku']) {
            $request->Item->SKU = $data['sku'];
        }

        // Site
        $request->Item->Site = SiteCodeType::C_US;

        // Price
        $request->Item->StartPrice = new AmountType;

        $request->Item->StartPrice->currencyID = CurrencyCodeType::C_USD;
        $request->Item->StartPrice->value      = (float)$data['price'];

        // Category
        $request->Item->PrimaryCategory             = new CategoryType;
        $request->Item->PrimaryCategory->CategoryID = (string)$data['category_id'];

        // Profiles
        $request->Item->SellerProfiles = new SellerProfilesType;

        $request->Item->SellerProfiles->SellerPaymentProfile                   = new SellerPaymentProfileType;
        $request->Item->SellerProfiles->SellerPaymentProfile->PaymentProfileID = $data['payment_profile_id'];

        $request->Item->SellerProfiles->SellerShippingProfile                    = new SellerShippingProfileType;
        $request->Item->SellerProfiles->SellerShippingProfile->ShippingProfileID = $data['shipping_profile_id'];

        $request->Item->SellerProfiles->SellerReturnProfile                  = new SellerReturnProfileType;
        $request->Item->SellerProfiles->SellerReturnProfile->ReturnProfileID = $data['return_profile_id'];

        // Description
        $request->Item->Description = $data['description'];

        // Pictures
        $request->Item->PictureDetails             = new PictureDetailsType;
        $request->Item->PictureDetails->PictureURL = $data['pictures'];

        // Duration (Always GTC)
        $request->Item->ListingDuration = ListingDurationCodeType::C_GTC;

        // Location
        $request->Item->Location = 'Florida, 34249';
        $request->Item->Country  = CountryCodeType::C_US;

        // Condition
        $request->Item->ConditionID = $data['condition_id'];

        // Currency
        $request->Item->Currency = CurrencyCodeType::C_USD;

        // Listing Type
        $request->Item->ListingType = ListingTypeCodeType::C_FIXED_PRICE_ITEM;

        // Product Details
        $request->Item->ProductListingDetails = new ProductListingDetailsType;

        // UPC
        if (@$data['upc']) {
            $request->Item->ProductListingDetails->UPC = $data['upc'];
        }

        // MPN
        if (@$data['mpn']) {
            $request->Item->ProductListingDetails->BrandMPN = $data['mpn'];
        }

        $response = $this->trading()->verifyAddItem($request);

        return $response;
    }

    public function suggestCategory(string $query): array
    {
        $cacheKey  = md5("suggestCategory({$query})");
        $cacheTime = 60 * 24 * 30; // Cache for 30 days

        return cache()->remember($cacheKey, $cacheTime, function () use ($query) {
            $request = $this->getSuggestedCategoriesRequest();

            $request->Query = $query;

            $response = $this->trading()->getSuggestedCategories($request);

            if ($response->Ack !== AckCodeType::C_SUCCESS) {
                throw new TradingApiException($request, $response);
            }

            return $response->SuggestedCategoryArray->toArray()['SuggestedCategory'];
        });
    }

    public function sellerProfiles(): array
    {
        $request = $this->getUserPreferencesRequest();

        $request->ShowSellerProfilePreferences = true;

        $response = $this->trading()->getUserPreferences($request);

        if ($response->Ack !== AckCodeType::C_SUCCESS) {
            throw new TradingApiException($request, $response);
        }

        return $response->SellerProfilePreferences->SupportedSellerProfiles->toArray()['SupportedSellerProfile'];
    }

    public function categoryFeatures($categoryId)
    {
        $request = $this->getCategoryFeaturesRequest();

        $request->CategoryID = (string)$categoryId;

        $request->FeatureID[] = FeatureIDCodeType::C_CONDITION_VALUES;
        $request->FeatureID[] = FeatureIDCodeType::C_CONDITION_ENABLED;

        $request->DetailLevel = [DetailLevelCodeType::C_RETURN_ALL];

        $response = $this->trading()->getCategoryFeatures($request);

        if ($response->Ack !== AckCodeType::C_SUCCESS) {
            throw new TradingApiException($request, $response);
        }

        return $response->Category[0]->ConditionValues->toArray()['Condition'];
    }

    public function syncItemsByStartTimeRange(\Carbon\Carbon $from = null, \Carbon\Carbon $until = null)
    {
        $request = $this->getSellerListRequest();

        # START TIME RAGE WITHIN LAST 3 MONTHS
        $request->StartTimeFrom = dt($from, 'GMT');
        $request->StartTimeTo   = dt($until, 'GMT');

        # PAGINATION
        $request->Pagination = new PaginationType;

        $request->Pagination->EntriesPerPage = 100;
        $request->Pagination->PageNumber     = 1;

        # OUTPUT SELECTOR
        $request->DetailLevel = [DetailLevelCodeType::C_RETURN_ALL];

        $request->OutputSelector = [
            'ItemArray.Item.ItemID',
            'ItemArray.Item.Title',
            'ItemArray.Item.ListingDetails.StartTime',
            'ItemArray.Item.SKU',
            'ItemArray.Item.Quantity',
            'ItemArray.Item.ProductListingDetails.UPC',
            'ItemArray.Item.PrimaryCategory.CategoryID',
            'ItemArray.Item.SellingStatus.QuantitySold',
            'ItemArray.Item.SellingStatus.CurrentPrice',
            'ItemArray.Item.SellingStatus.ListingStatus',
            // Pictures
            'ItemArray.Item.PictureDetails.PictureURL',
            // Pagination
            'PaginationResult',
            'HasMoreItems',
        ];

        do {
            $response = $this->trading()->getSellerList($request);

            if ($response->Ack !== 'Success') {
                throw new TradingApiException($request, $response);
            }

            // Create/Update Mirror Item to Databse
            collect($response->ItemArray->Item)->each(function (ItemType $item) {
                $this->updateOrCreateItem($item);
            });

            # UPDATE PAGINATION PAGE NUMBER
            $request->Pagination->PageNumber++;
        } while ($response->HasMoreItems);
    }

    public function updateOrCreateItem(ItemType $item, $only = [], $except = [])
    {
        return $this->items()->updateOrCreate(
            ['item_id' => $item->ItemID],
            Item::extractItemAttributes($item, $only, $except)
        );
    }

    public function syncOrdersByCreatedTimeRange(\Carbon\Carbon $from = null, \Carbon\Carbon $until = null)
    {
        $request = $this->getOrdersRequest();

        # DEFAULT FOR TIME RANGE IF NOT SETTED
        $from  = $from ?: Carbon::now()->subMonths(3);
        $until = $until ?: Carbon::now();

        # CREATED TIME RAGE
        $request->CreateTimeFrom = dt($from, 'GMT');
        $request->CreateTimeTo   = dt($until, 'GMT');

        # Final Value Fee
        $request->IncludeFinalValueFee = true;

        # PAGINATION
        $request->Pagination = new PaginationType;

        $request->Pagination->EntriesPerPage = 100;
        $request->Pagination->PageNumber     = 1;

        # OUTPUT SELECTOR
        $request->DetailLevel = [DetailLevelCodeType::C_RETURN_ALL];

        $request->OutputSelector = [
            // Pagination Stuffs
            'HasMoreOrders',
            'PaginationResult',
            // Order Details
            'OrderArray.Order.OrderID',
            'OrderArray.Order.OrderStatus',
            'OrderArray.Order.Total',
            'OrderArray.Order.BuyerUserID',
            'OrderArray.Order.PaymentHoldStatus',
            'OrderArray.Order.CancelStatus',
            'OrderArray.Order.CreatedTime',
            // Record Number
            'OrderArray.Order.ShippingDetails.SellingManagerSalesRecordNumber',
            // PayPal Fee
            'OrderArray.Order.ExternalTransaction.FeeOrCreditAmount',
            // Final Value Fee
            'OrderArray.Order.TransactionArray.Transaction.FinalValueFee',
            // Transaction Details
            'OrderArray.Order.TransactionArray.Transaction.TransactionID',
            'OrderArray.Order.TransactionArray.Transaction.QuantityPurchased',
            'OrderArray.Order.TransactionArray.Transaction.TransactionPrice',
            'OrderArray.Order.TransactionArray.Transaction.Item',
        ];

        do {
            $response = $this->trading()->getOrders($request);

            if ($response->Ack === AckCodeType::C_FAILURE) {
                throw new TradingApiException($request, $response);
            }

            $orders = $response->OrderArray->Order;

            collect($orders)->each(function (OrderType $orderType) {
                // Save Orders
                $order = $this->updateOrCreateOrder($orderType);

                // Save Order Transactions
                $order->saveTransactions($orderType->TransactionArray->Transaction);
            });

            # UPDATE PAGINATION PAGE NUMBER
            $request->Pagination->PageNumber++;
        } while ($response->HasMoreOrders);
    }

    protected function enableEvent(string $event): NotificationEnableType
    {
        return new NotificationEnableType([
            'EventType'   => $event,
            'EventEnable' => EnableCodeType::C_ENABLE,
        ]);
    }
}
