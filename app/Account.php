<?php

namespace App;

use App\Exceptions\ItemExistedException;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\AbstractRequestType;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Trading\Types\GetSellerListRequestType;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['username', 'token'];

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

    public function getSellerListRequest(): GetSellerListRequestType
    {
        return $this->prepareAuthRequiredRequest(new GetSellerListRequestType);
    }
}
