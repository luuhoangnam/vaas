<?php

namespace App\Cashback;

use App\Sourcing\SourceProductInterface;

class Engine
{
    public function bestCashbackProgram(SourceProductInterface $product): CashbackProgram
    {
        $programs = config('cashback.programs');

        $theBestRate    = null;
        $theBestProgram = null;

        foreach ($programs as $programConfig) {
            $belongingCategories = $product->belongingCategories();

            if (is_string($programConfig['rate']) && class_exists($programConfig['rate'])) {
                $rate = (new $programConfig['rate'])->resolve($belongingCategories);
            } else {
                $rate = $this->findRateByRateTable($belongingCategories, $programConfig['rate']);
            }

            if ($rate > $theBestRate) {
                $theBestRate    = $rate;
                $theBestProgram = new $programConfig['link_generator'];
            }
        }

        if ($theBestRate > 0) {
            return $theBestProgram;
        }

        return $this->makeDefaultProgram();
    }

    protected function findRateByRateTable($belongsCategories, array $rateTable)
    {
        foreach ($belongsCategories as $belongsCategory) {
            foreach ($rateTable as $category) {
                if ($category['id'] == $belongsCategory['id']) {
                    return $category['rate'];
                }
            }
        }

        return 0.0;
    }

    protected function makeDefaultProgram(): CashbackProgram
    {
        $default = config('cashback.default');

        $programClass = config("cashback.programs.{$default}.link_generator");

        if (class_exists($programClass)) {
            return new $programClass;
        }

        throw new \Exception("Can not make default cashback program with `{$default}`");
    }
}
