<?php

namespace App\Ranking;

use App\Account;
use App\Exceptions\FindingApiException;
use App\Item;
use Carbon\Carbon;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\FindItemsByKeywordsRequest;
use DTS\eBaySDK\Finding\Types\FindItemsByKeywordsResponse;
use DTS\eBaySDK\Finding\Types\SearchItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Tracker extends Model
{
    protected $fillable = ['keyword'];

    public function trackable()
    {
        return $this->morphTo();
    }

    public function records()
    {
        return $this->hasMany(Record::class);
    }

    public function current()
    {
        return $this->records()->latest()->limit(1);
    }

    public function refresh(): Record
    {
        $response = $this->performSearch();

        $this->detectRankFromSearchResponse($response);

        $total = $response->paginationOutput->totalEntries ?: 0;

        return $this->records()->create(compact('rank', 'total'));
    }

    public function report(Carbon $from, Carbon $until)
    {
        $dates      = date_range($from, $until);
        $whereDates = $dates->toDateStringCollection()->implode("','");

        // Default: Daily Retention
        return $this->records()
                    ->whereRaw("DATE(`records`.`created_at`) IN ('{$whereDates}')")
                    ->groupBy('date')
                    ->latest()
                    ->selectRaw('*, DATE(`records`.created_at) AS date')
                    ->get();
    }

    protected function finding(): FindingService
    {
        return app(FindingService::class);
    }

    protected function performSearch(): FindItemsByKeywordsResponse
    {
        $request = new FindItemsByKeywordsRequest;

        $request->keywords = $this['keyword'];

        $request->outputSelector = [
            OutputSelectorType::C_SELLER_INFO,
        ];

        $cacheKey  = $this->getSearchCacheKey($request);
        $cacheTime = $this->getSearchCacheTime();

        $responseData = cache()->remember($cacheKey, $cacheTime, function () use ($request) {
            return $this->finding()->findItemsByKeywords($request)->toArray();
        });

        $response = new FindItemsByKeywordsResponse($responseData);

        if ($response->ack === AckValue::C_FAILURE) {
            throw new FindingApiException($request, $response);
        }

        return $response;
    }

    protected function getSearchCacheKey(FindItemsByKeywordsRequest $request): string
    {
        return md5(serialize($request->toArray()));
    }

    protected function getSearchCacheTime(): float
    {
        return 1.0; // 1 minute
    }

    protected function isTrackingItem(SearchItem $searchItem): bool
    {
        $trackable = $this['trackable'];

        if ($trackable instanceof Item) {
            return $searchItem->itemId == $trackable['item_id'];
        } elseif ($trackable instanceof Account) {
            return $searchItem->sellerInfo->sellerUserName == $trackable['username'];
        }

        throw new \Exception('Can not get trackable eBay ID');
    }

    protected function detectRankFromSearchResponse(FindItemsByKeywordsResponse $response)
    {
        if ($response->searchResult->item) {
            foreach ($response->searchResult->item as $index => $searchItem) {
                if ($this->isTrackingItem($searchItem)) {
                    return $index + 1;
                }
            }
        }

        return null;
    }
}
