<?php

namespace App\Reporting;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class ItemReports
{
    /**
     * @var Collection|EloquentCollection
     */
    protected $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function earning()
    {
        return $this->items->sum('earning');
    }

    public function ordersCount()
    {
        return $this->items->sum('orders_count');
    }

    public function value()
    {
        return $this->items->sum('price');
    }

    public function averageEarningPerItem()
    {
        return $this->items->count() ? $this->earning() / $this->items->count() : null;
    }

    public function hasSaleItems()
    {
        return $this->items->filter(function ($item) {
            return $item['orders_count'] > 0;
        });
    }

    public function saleThroughRate()
    {
        return $this->items->count() ? $this->hasSaleItems()->count() / $this->items->count() : null;
    }

    public function averageOrderValue()
    {
        return $this->ordersCount() ? $this->earning() / $this->ordersCount() : null;
    }

    public function total()
    {
        return $this->count();
    }

    public function count()
    {
        return $this->items->count();
    }

    public function averageOrdersPerItem()
    {
        return $this->total() ? $this->ordersCount() / $this->total() : null;
    }

    public function averageItemValue()
    {
        return $this->total() ? $this->value() / $this->total() : null;
    }
}