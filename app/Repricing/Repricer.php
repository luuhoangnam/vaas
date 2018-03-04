<?php

namespace App\Repricing;

use App\Exceptions\UnableResolveSourceProductException;
use App\Item;
use App\Jobs\ReviseItemPrice;
use App\Jobs\ReviseItemQuantityToZero;
use App\Sourcing\AmazonProduct;
use App\Sourcing\SourceProduct;
use App\Support\ReviseCase;
use Illuminate\Database\Eloquent\Model;

class Repricer extends Model
{
    protected $fillable = ['rule'];

    protected $casts = ['rule' => 'array'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function run(): void
    {
        // 1. Fetch source product information
        $product = $this->resolveSourceProduct()->fetch();

        // 2. Compare last state of current listing
        switch ($this->needRevise($product)) {
            case ReviseCase::NEED_ZERO_QUANTITY:
                $this->reviseItemQuantityToZero();
                break;
            case ReviseCase::NEED_REVISE_PRICE:
                $newPrice = $this->calculatedPrice($product['price']);
                $this->reviseItemPrice($newPrice);
                break;
            case ReviseCase::NONE:
            default:
                break;
        }

        // 3. TODO Save Run History

    }

    protected function resolveSourceProduct(): SourceProduct
    {
        try {
            // TODO Support more product source
            return new AmazonProduct($this['item']['sku']);
        } catch (\Exception $exception) {
            throw new UnableResolveSourceProductException($this['item'], 0, $exception);
        }
    }

    protected function needRevise(array $product): int
    {
        # Helpers
        $sourceProductAvailable = $product['available'];
        $itemActive             = $this['item']['status'] === 'Active';
        $quantityNotZero        = $this['item']['quantity_available'] != 0;
        $itemAvailableForSale   = $itemActive && $quantityNotZero;
        $priceNotMatch          = $this->calculatedPrice($product['price']) != $this['item']['price'];
        # End Helpers

        // Case A: Product is NOT available but eBay item is Active and has quantity != 0
        // Solution: Update ebay quantity to 0, keep the price
        if ( ! $sourceProductAvailable && $itemAvailableForSale) {
            return ReviseCase::NEED_ZERO_QUANTITY;
        }

        // Case B: Product IS available and eBay item is active, too. But the calculated price is not match
        // Solution: Update ebay price to match the calculated price
        if ($sourceProductAvailable && $itemAvailableForSale && $priceNotMatch) {
            return ReviseCase::NEED_REVISE_PRICE;
        }

        // Default
        return ReviseCase::NONE;
    }

    protected function calculatedPrice($sourcePrice)
    {
        // A. FORMULA
        // 1. Price = Cog +             Fees                         +      Profit
        // 2. Price = Cog + Price x (PayPalRate + finalValueFeeRate) + Price x ProfitRate
        // 3. Price = (Cog + PayPalUsd) + Price x (PayPalRate + finalValueFeeRate + ProfitRate)
        // 4. Cog + PayPalUsd = Price x (1 - PayPalRate - finalValueFeeRate - ProfitRate)
        // 5. Price = (Cog + PayPalUsd) / (1 - PayPalRate - finalValueFeeRate - ProfitRate)

        // B. IMPLEMENT
        $rule = $this->rule();

        $cog               = $this->costOfGoodsSold($sourcePrice);
        $paypalUsd         = $rule['paypal_rate_usd'];
        $paypalRate        = $rule['paypal_rate'];
        $finalValueFeeRate = $rule['final_value_fee'];
        $profitRate        = $rule['profit'];

        return ($cog + $paypalUsd) / (1 - $paypalRate - $finalValueFeeRate - $profitRate);
    }

    protected function rule($field = null)
    {
        $defaultRule = config('ebay.repricer.default_rule');

        $rule = array_merge($defaultRule, $this['rule']);

        if ($field) {
            return array_get($rule, $field);
        }

        return $rule;
    }

    protected function costOfGoodsSold($sourcePrice)
    {
        $tax = $this->rule('source_tax');

        return $sourcePrice * (1 + $tax);
    }

    protected function reviseItemQuantityToZero()
    {
        ReviseItemQuantityToZero::dispatch($this['item']);
    }

    protected function reviseItemPrice($newPrice)
    {
        ReviseItemPrice::dispatch($this['item'], $newPrice);
    }
}
