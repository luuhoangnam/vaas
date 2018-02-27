<?php

namespace App;

use App\Exceptions\ItemExistedException;
use App\Exceptions\TradingApiException;
use DTS\eBaySDK\Trading\Enums\EnableCodeType;
use DTS\eBaySDK\Trading\Enums\NotificationEventTypeCodeType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\AbstractRequestType;
use DTS\eBaySDK\Trading\Types\ApplicationDeliveryPreferencesType;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Trading\Types\EndItemsRequestType;
use DTS\eBaySDK\Trading\Types\GetItemRequestType;
use DTS\eBaySDK\Trading\Types\GetNotificationPreferencesRequestType;
use DTS\eBaySDK\Trading\Types\GetSellerListRequestType;
use DTS\eBaySDK\Trading\Types\NotificationEnableArrayType;
use DTS\eBaySDK\Trading\Types\NotificationEnableType;
use DTS\eBaySDK\Trading\Types\SetNotificationPreferencesRequestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Account extends Model
{
    protected $fillable = ['username', 'token'];

    public static function find($username): Account
    {
        return static::query()->where('username', $username)->firstOrFail();
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function activeItems()
    {
        return $this->hasMany(Item::class, 'account_id')
                    ->where('status', 'Active');
    }

    public function completedItems()
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

    public static function exists($username): bool
    {
        return static::query()->where('username', $username)->exists();
    }

    public function saveItem(array $data): Item
    {
        if (Item::exists($data['item_id'])) {
            throw new ItemExistedException($data);
        }

        return $this->items()->create($data);
    }

    public function trading(): TradingService
    {
        return app(TradingService::class);
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

    protected function enableEvent(string $event): NotificationEnableType
    {
        return new NotificationEnableType([
            'EventType'   => $event,
            'EventEnable' => EnableCodeType::C_ENABLE,
        ]);
    }

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

    public function setNotificationPreferencesRequest(): SetNotificationPreferencesRequestType
    {
        return $this->prepareAuthRequiredRequest(new SetNotificationPreferencesRequestType);
    }

    public function getItemRequest(): GetItemRequestType
    {
        return $this->prepareAuthRequiredRequest(new GetItemRequestType);
    }
}
