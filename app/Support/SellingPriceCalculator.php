<?php

namespace App\Support;

class SellingPriceCalculator
{
    // A. FORMULA
    // 1. Price = Cog x Tax +             Fees                         +      Profit
    // 2. Price = Cog x Tax + Price x (PayPalRate + finalValueFeeRate) + Price x ProfitRate
    // 3. Price = (Cog x Tax + PayPalUsd) + Price x (PayPalRate + finalValueFeeRate + ProfitRate)
    // 4. Cog x Tax + PayPalUsd = Price x (1 - PayPalRate - finalValueFeeRate - ProfitRate)
    // 5. Price = (Cog x Tax + PayPalUsd) / (1 - PayPalRate - finalValueFeeRate - ProfitRate)
    public static function calc(array $input)
    {
        $validator = validator($input, [
            'cost_of_goods'    => 'bail|required|numeric',
            'margin'           => 'bail|required|numeric',
            'tax'              => 'bail|boolean',
            'final_value_rate' => 'bail|numeric',
            'paypal_rate'      => 'bail|numeric',
            'paypal_usd'       => 'bail|numeric',
            'minimum_price'    => 'bail|numeric',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->getMessageBag()->first());
        }

        $taxRate = config('ebat.repricer.tax_rate', 0.09);

        $cogIncludedTax = $input['cost_of_goods'] * ($input['tax'] ? (1 + $taxRate) : 1);

        $calculatedPrice = ($cogIncludedTax + $input['paypal_usd']) / (1 - $input['paypal_rate'] - $input['final_value_rate'] - $input['margin']);

        if ($calculatedPrice < $input['minimum_price']) {
            return $input['minimum_price'];
        }

        return $calculatedPrice;
    }
}
