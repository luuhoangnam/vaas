<?php

namespace App\Reporting;

use Illuminate\Support\Collection;

class OrderReports
{
    protected $orders;

    public function __construct(Collection $orders)
    {
        $this->orders = $orders;
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
        return $this->revenue() - $this->finalVaueFee() - $this->paypalFee() - $this->costOfGoods() + $this->cashback();
    }

    public function margin()
    {
        if ($this->revenue() === 0) {
            return 0.0;
        }

        return $this->profit() / $this->revenue();
    }

    public function numberOfOrders()
    {
        return $this->orders->count();
    }

    public function averageOrderValue()
    {
        return $this->revenue() / $this->numberOfOrders();
    }
}