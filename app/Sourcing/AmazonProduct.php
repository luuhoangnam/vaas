<?php

namespace App\Sourcing;

use App\Account;
use App\Cashback\AmazonAssociates;
use App\Cashback\Engine;
use App\Exceptions\CanNotFetchProductInformation;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AmazonProduct implements SourceProduct
{
    protected $asin;

    protected $cachedProducts = [];

    public function __construct($asin)
    {
        $this->asin = $asin;
    }

    public function getProductId(): string
    {
        return $this->asin;
    }

    public function fetch(): array
    {
        if (key_exists($this->getProductId(), $this->cachedProducts)) {
            return $this->cachedProducts[$this->getProductId()];
        }

        // Using Marketing API Strategy
        $strategies = config('ebay.sourcing.amazon.strategies');

        foreach ($strategies as $strategyClass) {
            try {
                /** @var FetchingStrategy $strategy */
                $strategy = new $strategyClass($this);

                $this->cachedProducts[$this->getProductId()] = $strategy->fetch();

                return $this->cachedProducts[$this->getProductId()];
            } catch (\Exception $exception) {
                // Can't use this strategy to fetch product information
                continue;
            }
        }

        throw new CanNotFetchProductInformation($this);
    }

    public function listedOnAccountsOfUser(User $user = null): Collection
    {
        $query = Account::query();

        if ($user instanceof User) {
            $query->where('user_id', $user['id']);
        }

        $query->whereHas('items', function (Builder $query) {
            $query->where('sku', $this->getProductId());
        });

        return $query->get(['id', 'username']);
    }

    public function getCashbackLink(): string
    {
        $cacheKey  = md5("cashback.amazon(asin:{$this->getProductId()}).link");
        $cacheTime = config('cashback.cache_time', 24 * 60); // 1 Day

        return cache()->remember($cacheKey, $cacheTime, function () {
            $bestCashbackProgram = (new Engine)->bestCashbackProgram($this);

            return $bestCashbackProgram->link($this->getProductId());
        });
    }

    public function belongingCategories()
    {
        return $this->fetch()['categories'];
    }
}