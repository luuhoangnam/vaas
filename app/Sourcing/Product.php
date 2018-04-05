<?php

namespace App\Sourcing;

class Product
{
    /**
     * @var Supplier
     */
    public $supplier;

    # IDENTIFIERS
    public $upc;
    public $asin;
    public $ean;
    public $isbn;

    # FIELDS
    public $title;
    public $images;
    public $description;
    public $attributes;

    # MONETARY
    public $price;
    public $tax;
    public $discount;

    # AVAILABILITY
    public $available;

    # SHIPPING
    public $shipping;

    # SELLER
    public $seller;

    # CUSTOM
    protected $custom;

    /**
     * Product constructor.
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if ($key === 'seller') {
                $this->seller = new Seller($value);

                continue;
            }

            if ($key === 'shipping') {
                $this->setShipping($value);

                continue;
            }

            if ($key === 'discount') {
                $this->setDiscount($value);

                continue;
            }

            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function setShipping($shipping): self
    {
        if ($shipping instanceof Shipping) {
            $this->shipping = $shipping;

            return $this;
        }

        $this->shipping = new Shipping(
            array_get($shipping, 'cost'),
            array_get($shipping, 'min_time'),
            array_get($shipping, 'max_time'),
            array_get($shipping, 'service')
        );

        return $this;
    }

    protected function setDiscount($discount): self
    {
        if ($discount instanceof Discount) {
            $this->discount = $discount;

            return $this;
        }

        $this->discount = new Discount(
            array_get($discount, 'amount'),
            array_get($discount, 'type', 'fixed')
        );

        return $this;
    }

    public function __get($name)
    {
        if (array_keys($this->custom, $name)) {
            return $this->custom[$name];
        }

        return $this->{$key};
    }

    public function estimatedTax()
    {
        if ($this->seller->noTax()) {
            return 0;
        }

        $rate = config('sourcing.average_tax_rate');

        return $this->totalBeforeTax() * $rate;
    }

    public function totalBeforeTax()
    {
        return $this->price - $this->discount->amount + $this->shipping->cost;
    }

    public function totalAfterTax()
    {
        return $this->totalBeforeTax() + $this->estimatedTax();
    }

    public function costOfGoods()
    {
        $total = $this->totalAfterTax();

        $otherFees = $this->supplier instanceof Supplier ? $this->supplier->otherFees($total) : 0;

        return $total + $otherFees;
    }
}