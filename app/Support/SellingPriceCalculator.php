<?php

namespace App\Support;

class SellingPriceCalculator
{
    public static function calculate(
        $costOfGoods,
        $expectedMargin = 0.05,
        $tax = true,
        $finalValueRate = 0.0915,
        $paypalRate = 0.039,
        $paypalUsd = 0.3,
        $minimumPrice = null
    ) {
        # FORMULA
        // 1. Price = Cog x Tax +             Fees                         +      Profit
        // 2. Price = Cog x Tax + Price x (PayPalRate + finalValueFeeRate) + Price x ProfitRate
        // 3. Price = (Cog x Tax + PayPalUsd) + Price x (PayPalRate + finalValueFeeRate + ProfitRate)
        // 4. Cog x Tax + PayPalUsd = Price x (1 - PayPalRate - finalValueFeeRate - ProfitRate)
        // 5. Price = (Cog x Tax + PayPalUsd) / (1 - PayPalRate - finalValueFeeRate - ProfitRate)

        $taxRate = 1.09;

        $calculatedPrice = ($costOfGoods * ($tax ? $taxRate : 1) + $paypalUsd) / (1 - $paypalRate - $finalValueRate - $expectedMargin);

        if ($calculatedPrice < $minimumPrice) {
            return $minimumPrice;
        }

        return $calculatedPrice;
    }
}
