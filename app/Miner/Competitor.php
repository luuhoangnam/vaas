<?php

namespace App\Miner;

use App\eBay\TradingAPI;
use App\Events\Miner\CompetitorCreated;
use DTS\eBaySDK\Finding\Types\SearchItem;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\GetUserRequestType;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string username
 */
class Competitor extends Model
{
    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => CompetitorCreated::class,
    ];

    public static function exists($username): bool
    {
        return static::query()->where('username', $username)->exists();
    }

    public static function notExists($username): bool
    {
        return ! static::exists($username);
    }

    public static function find($username): Competitor
    {
        return static::query()->where('username', $username)->firstOrFail();
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function persistSearchItem(SearchItem $item): Item
    {
        $find = ['item_id' => $item->itemId];
        $data = [
            'item_id' => $item->itemId,
            'title'   => $item->title,
            'price'   => $item->sellingStatus->currentPrice->value,
        ];

        return $this->items()->updateOrCreate($find, $data);
    }

    public static function add($username): Competitor
    {
        // 1. Validate Competitor by using eBay API Request
        $request = new GetUserRequestType([
            'UserID' => (string)$username,
        ]);

        $response = TradingAPI::random()->getUser($request);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new \InvalidArgumentException('Invalid Competitor Username', 'username.invalid');
        }

        // 2. Add to database if valid
        $userType = $response->User;

        return Competitor::query()->updateOrCreate(compact('username'), [
            'username'                  => $userType->UserID,
            'feedback_score'            => $userType->FeedbackScore,
            'positive_feedback_percent' => $userType->PositiveFeedbackPercent,
            'country'                   => $userType->Site,
        ]);
    }
}
