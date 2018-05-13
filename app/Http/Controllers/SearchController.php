<?php

namespace App\Http\Controllers;

use App\eBay\AppPool;
use App\Exceptions\FindingApiException;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Enums\ItemFilterType;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Types\FindItemsByKeywordsRequest;
use DTS\eBaySDK\Finding\Types\ItemFilter;
use DTS\eBaySDK\Finding\Types\PaginationInput;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    public function seller(Request $request)
    {
        if ( ! $request->has('keyword')) {
            return view('search.seller');
        }

        $this->validate($request, [
            'keyword'      => '',
            'min_price'    => '',
            'min_feedback' => '',
        ]);

        try {
            $sellers = $this->search($request['keyword'], $request['min_price'], $request['min_feedback'])
                            ->unique('seller.username')
                            ->map(function ($item) {
                                return array_merge($item['seller'], [
                                    'url' => "https://www.ebay.com/usr/{$item['seller']['username']}",
                                    'zik' => "https://www.zikanalytics.com/SearchCompetitor/Index?search=true&Competitor={$item['seller']['username']}",
                                ]);
                            });
        } catch (FindingAPIException $exception) {
            $errors = new Collection([$exception->getMessage()]);

            return view('search.seller', compact('errors'));
        }

        return view('search.seller', compact('sellers'));
    }

    /**
     * @param string     $keyword
     * @param float      $minPrice
     * @param integer    $minFeedback
     * @param string|int $buyerPostalCode
     *
     * @return Collection
     * @throws FindingAPIException
     */
    protected function search(string $keyword, $minPrice = null, $minFeedback = 1000, $buyerPostalCode = 10001)
    {
        $request = new FindItemsByKeywordsRequest;
        $request->buyerPostalCode = (string)$buyerPostalCode;
        $request->keywords = $keyword;

        $request->paginationInput = new PaginationInput;
        $request->paginationInput->entriesPerPage = 100;
        $request->paginationInput->pageNumber = 1;

        if ($minPrice) {
            $request->itemFilter[] = $this->item_filter(ItemFilterType::C_MIN_PRICE, $minPrice);
        }

        if ($minFeedback) {
            $request->itemFilter[] = $this->item_filter(ItemFilterType::C_FEEDBACK_SCORE_MIN, $minFeedback);
        }

        $request->itemFilter[] = $this->item_filter(ItemFilterType::C_CONDITION, 'New');
        $request->itemFilter[] = $this->item_filter(ItemFilterType::C_LOCATED_IN, 'US');
        $request->itemFilter[] = $this->item_filter(ItemFilterType::C_LISTING_TYPE, 'FixedPrice');

        $request->outputSelector = [OutputSelectorType::C_SELLER_INFO];

        $items = new Collection;

        do {
            $response = AppPool::random()->createFinding()->findItemsByKeywords($request);

            if ($response->ack === AckValue::C_FAILURE) {
                throw new FindingAPIException($request, $response);
            }

            foreach ($response->searchResult->item as $searchItem) {
                $items->push([
                    'item_id' => $searchItem->itemId,
                    'title'   => $searchItem->title,
                    'seller'  => [
                        'username'       => $searchItem->sellerInfo->sellerUserName,
                        'feedback_score' => $searchItem->sellerInfo->feedbackScore,
                        'feedback_rate'  => $searchItem->sellerInfo->positiveFeedbackPercent,
                    ],
                ]);
            }

            $request->paginationInput->pageNumber += 1;
            $hasMorePages = $response->paginationOutput->pageNumber < $response->paginationOutput->totalPages;
        } while ($response->paginationOutput->pageNumber < 10 && $hasMorePages);

        return $items;
    }

    protected function item_filter($name, $value): ItemFilter
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = (string)$v;
            }
        } else {
            $value = [(string)$value];
        }

        return new ItemFilter(compact('name', 'value'));
    }
}
