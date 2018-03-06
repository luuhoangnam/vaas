<?php

namespace App\Sourcing;

use App\Account;
use App\Cashback\Engine;
use App\Exceptions\CanNotFetchProductInformation;
use App\User;
use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AmazonProduct implements SourceProduct, ArrayAccess
{
    protected $asin;

    protected $cachedProducts = [];

    protected $attributes = [];

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

                return $this->attributes = $this->cachedProducts[$this->getProductId()];
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

        $query->whereHas('item', function (Builder $query) {
            $query->where('sku', $this->getProductId());
        });

        return $query->get(['id', 'username']);
    }

    public function getCashbackLink(): string
    {
        $cacheKey = md5("cashback.amazon(asin:{$this->getProductId()}).link");
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

    public function __get($name)
    {
        $attrs = $this->fetch();

        if (key_exists($name, $attrs)) {
            return $attrs[$name];
        }

        return;
    }

    public function offsetExists($offset)
    {
        $attrs = $this->fetch();

        return key_exists($offset, $attrs);
    }

    public function offsetGet($offset)
    {
        $attrs = $this->fetch();

        return $attrs[$offset];
    }

    public function offsetSet($offset, $value)
    {
        return $this->unsupportedAction();
    }

    public function offsetUnset($offset)
    {
        return $this->unsupportedAction();
    }

    protected function unsupportedAction(
        $message = 'This is just a mapped object of an amazon product. You can not change value on it'
    ) {
        throw new \Exception($message);
    }
}