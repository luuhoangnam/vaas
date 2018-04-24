<?php

namespace App;

use DTS\eBaySDK\Trading\Enums\OrderStatusCodeType;
use DTS\eBaySDK\Trading\Types\OrderType;
use DTS\eBaySDK\Trading\Types\TransactionType;
use DTS\eBaySDK\Types\RepeatableType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

/**
 * @property Carbon created_time
 * @property double cog
 */
class Order extends Model
{
    use Searchable;

    protected $fillable = [
        'order_id',
        'record',
        'status',
        'total',
        'buyer_username',
        'payment_hold_status',
        'cancel_status',
        'created_time',
        // Fees
        'final_value_fee',
        'paypal_fee',
        'other_fee',
        // Fulfillment
        'cog',
        // Cashback
        'cashback',
    ];

    protected $casts = ['created_time' => 'datetime'];

    protected static function boot()
    {
        parent::boot();
    }

    public function searchableAs()
    {
        return 'orders';
    }

    public function toSearchableArray()
    {
        return $this->toArray();
    }

    public static function exists($orderID): bool
    {
        return static::query()->where('order_id', $orderID)->exists();
    }

    public static function find($orderID): Order
    {
        return static::query()->where('order_id', $orderID)->firstOrFail();
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeEffective(Builder $query)
    {
        return $query->where('status', 'Completed');
    }

    public function getEffectiveAttribute()
    {
        return $this['status'] == OrderStatusCodeType::C_COMPLETED;
    }

    public function getFeesAttribute()
    {
        return $this['final_value_fee'] + $this['paypal_fee'];
    }

    public function getMarginAttribute()
    {
        return $this['profit'] / $this['total'];
    }

    public function getBuyerEbayLinkAttribute()
    {
        return "https://www.ebay.com/usr/{$this['buyer_username']}";
    }

    public function getEbayLinkAttribute()
    {
        if ($this['transactions']->count() === 0) {
            return null;
        }

        $transaction = $this['transactions'][0];

        $transactionID = $transaction['transaction_id'];
        $itemID = $transaction['item_id'];

        $rootUrl = 'https://k2b-bulk.ebay.com/ws/eBayISAPI.dll?EditSalesRecord&urlstack=5508||';

        return "{$rootUrl}&transid={$transactionID}&itemid={$itemID}";
    }

    /**
     * @param RepeatableType|TransactionType[] $transactionTypes
     *
     * @return Collection
     */
    public function saveTransactions(RepeatableType $transactionTypes): Collection
    {
        return collect($transactionTypes)->each(function (TransactionType $transactionType) {
            $this->saveTransaction($transactionType);
        });
    }

    public function saveTransaction(TransactionType $transactionType): Transaction
    {
        return $this->transactions()->updateOrCreate(
            ['transaction_id' => $transactionType->TransactionID],
            [
                'transaction_id' => $transactionType->TransactionID,
                'quantity'       => $transactionType->QuantityPurchased,
                'item_id'        => $transactionType->Item->ItemID,
                'item_site'      => $transactionType->Item->Site,
                'item_title'     => $transactionType->Item->Title,
                'item_sku'       => $transactionType->Item->SKU,
            ]
        );
    }

    public function getProfitAttribute()
    {
        if (is_null($this['cog'])) {
            return null;
        }

        $giftcardRate = (1 + config('ebay.giftcard_rate')) / 100; // 1.0375

        return $this['total'] - $this['final_value_fee'] - $this['paypal_fee'] - $this['cog'] * $giftcardRate + $this['cashback'];
    }

    public static function extractAttribute(OrderType $data)
    {
        $transactions = collect($data->TransactionArray->Transaction)->map(function (TransactionType $transaction) {
            return [
                'price'           => $transaction->TransactionPrice->value,
                'final_value_fee' => $transaction->FinalValueFee->value,
            ];
        });

        $total = $transactions->pluck('price')->sum();
        $finalValueFee = $transactions->pluck('final_value_fee')->sum();

        return [
            'order_id'            => $data->OrderID,
            'record'              => $data->ShippingDetails->SellingManagerSalesRecordNumber,
            'status'              => $data->OrderStatus,
            'total'               => (double)$total,
            'buyer_username'      => $data->BuyerUserID,
            'payment_hold_status' => $data->PaymentHoldStatus,
            'cancel_status'       => $data->CancelStatus,
            'created_time'        => app_carbon($data->CreatedTime),
            // Fees
            'final_value_fee'     => $finalValueFee,
            'paypal_fee'          => $data->ExternalTransaction[0]->FeeOrCreditAmount->value,
        ];
    }

    public function getPaypalFeeAttribute()
    {
        return $this->attributes['paypal_fee'] ?: $this->defaultPayPalFee();
    }

    protected function defaultPayPalFee()
    {
        $rate = 3.9 / 100;

        return $this['total'] * $rate + 0.3;
    }

    public function getFinalValueFeeAttribute()
    {
        return $this->attributes['final_value_fee'] ?: $this->defaultFinalValueFee();
    }

    protected function defaultFinalValueFee()
    {
        $rate = 9.15 / 100;

        return $this['total'] * $rate;
    }
}
