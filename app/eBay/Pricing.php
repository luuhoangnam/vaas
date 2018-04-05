<?php

namespace App\eBay;

class Pricing
{
    protected $source_price;
    protected $tax;
    protected $rate_on_source;
    protected $rate_on_revenue;
    protected $final_value_rate;
    protected $paypal_rate;

    public function __construct($input)
    {
        try {
            // Source Price
            $this->source_price = $input['source_price'];

            // Taxed or Not
            $this->tax = $input['tax'];

            // Giftcard
            $this->rate_on_source = $input['rate_on_source']; // Giftcard or Whatever

            // Final Value Fee
            $this->final_value_rate = $this['final_value_rate'];

            // Paypal Transaction Fee
            $this->paypal_rate = $this['paypal_rate'];
        } catch (\Exception $exception) {
            throw new \InvalidArgumentException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param float $source_price
     *
     * @return Pricing
     */
    public function setSourcePrice(float $source_price): self
    {
        $this->source_price = $source_price;

        return $this;
    }

    /**
     * @param float $tax
     *
     * @return Pricing
     */
    public function setTax(bool $tax = true): self
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @param float $rate_on_source
     *
     * @return Pricing
     */
    public function setRateOnsource(float $rate_on_source): self
    {
        $this->rate_on_source = $rate_on_source;

        return $this;
    }

    /**
     * @param float $final_value_rate
     *
     * @return Pricing
     */
    public function setFinalValueRate(float $final_value_rate): self
    {
        $this->final_value_rate = $final_value_rate;

        return $this;
    }

    /**
     * @param float $paypal_rate
     *
     * @return Pricing
     */
    public function setPaypalRate(float $paypal_rate): self
    {
        $this->paypal_rate = $paypal_rate;

        return $this;
    }

    public function calculate()
    {

    }
}