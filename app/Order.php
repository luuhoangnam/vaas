<?php

namespace App;

use DTS\eBaySDK\Trading\Types\OrderType;
use DTS\eBaySDK\Trading\Types\TransactionType;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
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
    ];

    protected $casts = ['created_time' => 'datetime'];

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

    public static function extractAttribute(OrderType $data)
    {
        return [
            'order_id'            => $data->OrderID,
            'record'              => $data->ShippingDetails->SellingManagerSalesRecordNumber,
            'status'              => $data->OrderStatus,
            'total'               => (double)$data->Total->value,
            'buyer_username'      => $data->BuyerUserID,
            'payment_hold_status' => $data->PaymentHoldStatus,
            'cancel_status'       => $data->CancelStatus,
            'created_time'        => app_carbon($data->CreatedTime),
            // Fees
            'final_value_fee'     => $data->TransactionArray->Transaction[0]->FinalValueFee->value,
            'paypal_fee'          => $data->ExternalTransaction[0]->FeeOrCreditAmount->value,
        ];
    }

    public function getPaypalFeeAttribute(): double
    {
        return $this->attributes['paypal_fee'] ?: $this->defaultPayPalFee();
    }

    protected function defaultPayPalFee(): double
    {
        $rate = 3.9 / 100;

        return $this['total'] * $rate + 0.3;
    }

    public function getFinalValueFeeAttribute(): double
    {
        return $this->attributes['final_value_fee'] ?: $this->defaultFinalValueFee();
    }

    protected function defaultFinalValueFee(): double
    {
        $rate = 9.15 / 100;

        return $this['total'] * $rate;
    }
}
