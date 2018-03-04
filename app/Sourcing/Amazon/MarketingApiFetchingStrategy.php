<?php

namespace App\Sourcing\Amazon;

use App\Exceptions\UnableFetchAmazonItemUsingMarketingApiException;
use App\Sourcing\FetchingStrategy;
use App\Sourcing\SourceProduct;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Revolution\Amazon\ProductAdvertising\AmazonClient;

class MarketingApiFetchingStrategy implements FetchingStrategy
{
    protected $product;

    public function __construct(SourceProduct $product)
    {
        $this->product = $product;
    }

    public function fetch()
    {
        $response = $this->rawResult();

        $item        = $this->item($response);
        $buyboxOffer = $this->offer($response);
        $buyboxPrice = (int)$buyboxOffer['OfferListing']['Price']['Amount'] / 100;
        $attributes  = $item['ItemAttributes'];

        $affiliatable = true;
        $title        = $attributes['Title'];
        $upc          = $attributes['UPC'];
        $price        = $buyboxPrice;
        $available    = $this->isAvailable($response);
        $images       = $this->getImages($item['ImageSets']['ImageSet']);
        $attributes   = $this->attributes($item);
        $category     = $this->topLevelCategory($item);

        return compact('affiliatable', 'price', 'available', 'title', 'upc', 'images', 'attributes', 'category');
    }

    protected function amazon(): AmazonClient
    {
        return app(AmazonClient::class);
    }

    public function rawResult()
    {
        $cacheKey  = md5("source.amazon(asin:{$this->asin()})");
        $cacheTime = 5;

        return cache()->remember($cacheKey, $cacheTime, function () {
            try {
                return $this->amazon()->item($this->asin());
            } catch (ClientException $exception) {
                throw new UnableFetchAmazonItemUsingMarketingApiException($this->asin(), $exception);
            }
        });
    }

    protected function getImages($imageSet): Collection
    {
        return collect($imageSet)->map(function ($image) {
            return @$image['HiResImage'] ? $image['HiResImage']['URL']
                : @$image['LargeImage'] ? $image['LargeImage']['URL']
                    : @$image['MediumImage'] ? $image['MediumImage']['URL']
                        : @$image['TinyImage'] ? $image['TinyImage']['URL']
                            : @$image['ThumbnailImage'] ? $image['ThumbnailImage']['URL']
                                : @$image['SmallImage'] ? $image['SmallImage']['URL']
                                    : @$image['SwatchImage'] ? $image['SwatchImage']['URL']
                                        : null;
        });
    }

    protected function isAvailable($response): bool
    {
        $listing = $this->listing($response);

        $attributes = $listing['AvailabilityAttributes'];

        if (config('ebay.sourcing.amazon.treatNonPrimeAsNotAvailable') && ! $this->isPrime($listing)) {
            return false;
        }

        return $attributes['AvailabilityType'] === 'now';
    }

    protected function item($response): array
    {
        return $response['Items']['Item'];
    }

    protected function offer($response): array
    {
        return $this->item($response)['Offers']['Offer'];
    }

    protected function listing($response): array
    {
        return $this->offer($response)['OfferListing'];
    }

    protected function isPrime($listing)
    {
        return $listing['IsEligibleForPrime'] == '1';
    }

    protected function attributes($item): array
    {
        return array_only($item['ItemAttributes'], $this->includeAttributeNames());
    }

    protected function includeAttributeNames(): array
    {
        return [
            'Brand',
            'Color',
            'EAN',
            'Feature',
            'PartNumber',
            'Manufacturer',
            'Label',
            'PackageQuantity',
            'MPN',
            'Model',
            'ProductGroup',
            'ProductTypeName',
            'Studio',
            'Size',
            'Publisher',
        ];
    }

    protected function topLevelCategory($item): array
    {
        $topCat = $this->rescursiveGetTopCategory($item['BrowseNodes']['BrowseNode']);

        return [
            'id'   => (int)$topCat['BrowseNodeId'],
            'name' => $topCat['Name'],
        ];
    }

    protected function rescursiveGetTopCategory($browseNode): array
    {
        if (@$browseNode[0]) {
            $browseNode = $browseNode[0];
        }

        if (@$browseNode['Ancestors']) {
            return $this->rescursiveGetTopCategory($browseNode['Ancestors']['BrowseNode']);
        }

        return $browseNode;
    }

    /**
     * @return mixed
     */
    protected function asin()
    {
        return $this->product->getProductId();
    }
}