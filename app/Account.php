<?php

namespace App;

use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\AbstractRequestType;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['username', 'token'];

    public static function exists($username): bool
    {
        return static::query()->where('username', $username)->exists();
    }

    public function trading(): TradingService
    {
        return app(TradingService::class);
    }

    public function prepareAuthRequiredRequest(AbstractRequestType $request): AbstractRequestType
    {
        // Credentials
        $credentials                = new CustomSecurityHeaderType;
        $credentials->eBayAuthToken = $this['auth']['auth_token'];

        // Prepare the Request
        $request->RequesterCredentials = $credentials;

        return $request;
    }
}
