<?php

namespace App\Ranking;

use App\Exceptions\FindingApiException;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\FindItemsByKeywordsRequest;
use DTS\eBaySDK\Finding\Types\FindItemsByKeywordsResponse;
use Illuminate\Database\Eloquent\Model;

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

    public function lastRecord()
    {
        return $this->records()->latest()->limit(1);
    }

    public function refresh(): Record
    {
        $response = $this->performSearch();

        $rank = null;

        if ($response->searchResult->item) {
            foreach ($response->searchResult->item as $index => $searchItem) {
                $trackableId = $this['trackable']['item_id'];

                if ($searchItem->itemId == $trackableId) {
                    $rank = $index + 1;

                    break;
                }
            }
        }

        $total = $response->paginationOutput->totalEntries ?: 0;

        return $this->records()->create(compact('rank', 'total'));
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
}
