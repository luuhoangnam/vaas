<?php

namespace App\Repricing;

use App\Exceptions\Amazon\ProductAdvertisingAPIException;
use App\Item;
use App\Jobs\ReviseItemPrice;
use App\Jobs\ReviseItemQuantityToZero;
use App\Sourcing\Amazon\AmazonAPI;
use App\Support\ReviseCase;
use App\Support\SellingPriceCalculator;
use Illuminate\Database\Eloquent\Model;

class Repricer extends Model
{
    protected $guarded = [];

    protected $casts = ['rule' => 'array'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @throws ProductAdvertisingAPIException
     */
    public function run(): void
    {
        // 1. Fetch source product information
        $product = $this->resolveProduct();

        // 2. Compare last state of current listing
        $reviseCase = $this->needRevise($product);

        switch ($reviseCase) {
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

    /**
     * @return array
     * @throws ProductAdvertisingAPIException
     */
    protected function resolveProduct(): array
    {
        return AmazonAPI::inspect($this['asin']);
    }

    protected function needRevise(array $product): int
    {
        # Helpers
        $sourceProductAvailable = $product['available'];
        $itemActive = $this['item']['status'] === 'Active';
        $quantityNotZero = $this['item']['quantity_available'] != 0;
        $itemAvailableForSale = $itemActive && $quantityNotZero;
        $priceNotMatch = $this->calculatedPrice($product['price']) != $this['item']['price'];
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
        return SellingPriceCalculator::calc(
            array_merge(
                $this->rule(),
                ['cost_of_goods' => $sourcePrice]
            )
        );
    }

    protected function rule($field = null)
    {
        $defaultRule = config('ebay.repricer.default');

        $rule = array_merge($defaultRule, $this['rule']);

        if ($field) {
            return array_get($rule, $field);
        }

        return $rule;
    }

    protected function reviseItemQuantityToZero()
    {
        ReviseItemQuantityToZero::dispatch($this['item'])->onQueue('repricer');
    }

    protected function reviseItemPrice($newPrice)
    {
        ReviseItemPrice::dispatch($this['item'], $newPrice)->onQueue('repricer');
    }
}
