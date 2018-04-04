<?php

namespace App\Reporting;

use App\Order;
use Carbon\Carbon;
use DTS\eBaySDK\Trading\Enums\OrderStatusCodeType;
use Illuminate\Support\Collection;

class OrderReports
{
    protected $orders;

    public function __construct(Collection $orders, $effective = true)
    {
        $this->orders = $orders->filter(function (Order $order) use ($effective) {
            if ($effective) {
                return $order['status'] == OrderStatusCodeType::C_COMPLETED;
            }

            return true;
        });
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function onDate($date): self
    {
        if ( ! $date instanceof Carbon) {
            $date = new \Illuminate\Support\Carbon($date);
        }

        $orders = $this->orders->filter(function (Order $order) use ($date) {
            return $date->isSameDay($order['created_time']);
        });

        return new self($orders);
    }

    public function onWeek($startDayOfWeek)
    {
        if ( ! $startDayOfWeek instanceof Carbon) {
            $startDayOfWeek = new \Illuminate\Support\Carbon($startDayOfWeek);
        }

        $orders = $this->orders->filter(function (Order $order) use ($startDayOfWeek) {
            /** @var Carbon $createdTime */
            $createdTime = $order['created_time'];

            return $createdTime->between(
                $startDayOfWeek->startOfWeek(),
                $startDayOfWeek->endOfWeek()
            );
        });

        return new self($orders);
    }

    public function revenue()
    {
        return $this->orders->sum('total');
    }

    public function costOfGoods()
    {
        return $this->orders->sum('cog');
    }

    public function finalVaueFee()
    {
        return $this->orders->sum('final_value_fee');
    }

    public function paypalFee()
    {
        return $this->orders->sum('paypal_fee');
    }

    public function fees()
    {
        return $this->finalVaueFee() + $this->paypalFee();
    }

    public function cashback()
    {
        return $this->orders->sum('cashback');
    }

    public function profit()
    {
        $costOfGoodsIncGC = $this->costOfGoods() * (1 + config('ebay.giftcard_rate'));

        return $this->revenue() - $this->finalVaueFee() - $this->paypalFee() - $costOfGoodsIncGC + $this->cashback();
    }

    public function margin()
    {
        if ($this->revenue() === 0) {
            return 0.0;
        }

        return $this->revenue() ? $this->profit() / $this->revenue() : null;
    }

    public function numberOfOrders()
    {
        return $this->orders->count();
    }

    public function averageOrderValue()
    {
        return $this->numberOfOrders() ? $this->revenue() / $this->numberOfOrders() : null;
    }

    public function averageOrderProfit()
    {
        return $this->numberOfOrders() ? $this->profit() / $this->numberOfOrders() : null;
    }

    public function count()
    {
        return $this->orders->count();
    }
}