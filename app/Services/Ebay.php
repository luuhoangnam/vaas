<?php

namespace App\Services;

use App\Exceptions\EbayException;
use App\Exceptions\InvalidItemIdException;
use DTS\eBaySDK\Finding\Enums\OutputSelectorType;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Finding\Types\FindItemsByKeywordsRequest;
use DTS\eBaySDK\Finding\Types\PaginationInput;
use DTS\eBaySDK\Shopping\Services\ShoppingService;
use DTS\eBaySDK\Shopping\Types\GetSingleItemRequestType;

class Ebay
{
    public function search($query, $options = []): array
    {
        $finding = $this->finding();

        $request = new FindItemsByKeywordsRequest;

        $request->keywords = $query;

        $request->outputSelector = [
            OutputSelectorType::C_SELLER_INFO,
        ];

        $request->paginationInput                 = new PaginationInput;
        $request->paginationInput->entriesPerPage = 100;

        $request->buyerPostalCode = (string)(array_get($options, 'buyerZipCode', 10001));

        $response = $finding->findItemsByKeywords($request)->toArray();

        // Normalize Response
        $items = collect($response['searchResult']['item'])->map(function (array $item) {
            return [
                'item_id'   => $item['itemId'],
                'title'     => $item['title'],
                'image_url' => @$item['galleryURL'],
                'price'     => (double)$item['sellingStatus']['currentPrice']['value'],
                'seller'    => [
                    'username'       => $item['sellerInfo']['sellerUserName'],
                    'feedback_score' => $item['sellerInfo']['feedbackScore'],
                ],
            ];
        });

        // Check Ranking
        $ranking = collect(@$options['ranking'])->map(function ($check) use ($items) {
            $rank = $this->findRank($items, $check['id'], $check['type']);

            return array_merge($check, compact('rank'));
        });

        $total = (int)$response['paginationOutput']['totalEntries'];

        return compact('items', 'ranking', 'total');
    }

    public function getItem($itemID): array
    {
        $key = md5("ebay.getSingleItem(ItemID:{$itemID})");

        return cache()->remember($key, 1, function () use ($itemID) {
            $request = new GetSingleItemRequestType;

            $request->ItemID = (string)$itemID;

            $request->IncludeSelector = '';

            $response = $this->shopping()->getSingleItem($request)->toArray();

            if ($response['Ack'] !== 'Success') {
                $firstError = array_first($response['Errors']);

                switch ($firstError['ErrorCode']) {
                    case '10.12':
                        throw new InvalidItemIdException;
                    default:
                        throw new EbayException;
                }
            }

            $item = $response['Item'];

            return [
                'item_id' => $item['ItemID'],
                'title'   => $item['Title'],
                'image'   => $item['GalleryURL'],
                'price'   => $item['ConvertedCurrentPrice']['value'],
            ];
        });
    }

    protected function findRank($items, $id, $type = 'item_id')
    {
        $rank = null;

        foreach ($items as $index => $item) {
            if ($type === 'item_id' && $item['item_id'] == $id) {
                return $index + 1;
            }

            if ($type === 'username' && $item['seller']['username'] == $id) {
                return $index + 1;
            }
        }

        return $rank;
    }

    protected function finding(): FindingService
    {
        return app(FindingService::class);
    }

    protected function shopping(): ShoppingService
    {
        return app(ShoppingService::class);
    }
}
