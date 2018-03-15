<?php

namespace App\Sourcing\Amazon;

use App\Exceptions\InvalidAmazonAssociatesItemException;
use App\Exceptions\NonAffiliatableException;
use App\Exceptions\UnableFetchAmazonItemUsingMarketingApiException;
use App\Sourcing\FetchingStrategy;
use App\Sourcing\SourceProductInterface;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Revolution\Amazon\ProductAdvertising\AmazonClient;

class MarketingApiFetchingStrategy implements FetchingStrategy
{
    protected $product;

    public function __construct(SourceProductInterface $product)
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

        $categoryTraversal = new AmazonCategoryTraversal($item['BrowseNodes']['BrowseNode']);

        return [
            'affiliatable'       => true,
            'price'              => $buyboxPrice,
            'available'          => $this->isAvailable($response),
            'title'              => $attributes['Title'],
            'upc'                => $attributes['UPC'],
            'images'             => $this->getImages($item['ImageSets']['ImageSet']),
            'attributes'         => $this->attributes($item),
            'categories'         => $categoryTraversal->getFlatCategories(),
            'top_level_category' => $this->topLevelCategory($item),
        ];
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
                $response = $this->amazon()->item($this->asin());

                if ($this->hasError($response, 'AWS.InvalidParameterValue')) {
                    throw new InvalidAmazonAssociatesItemException($this->asin());
                }

                if ($this->hasError($response, 'AWS.ECommerceService.ItemNotAccessible')) {
                    throw new NonAffiliatableException($this->asin());
                }

                return $response;
            } catch (ClientException $exception) {
                throw new UnableFetchAmazonItemUsingMarketingApiException($this->asin(), $exception);
            }
        });
    }

    protected function hasError($response, $code = null): bool
    {
        if ($code) {
            return @$response['Items']['Request']['Errors']['Error']['Code'] == $code;
        }

        return (bool)@$response['Items']['Request']['Errors']['Error'];
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
        return (new AmazonCategoryTraversal($item['BrowseNodes']['BrowseNode']))->topLevelCategory();
    }

    /**
     * @return mixed
     */
    protected function asin()
    {
        return $this->product->getProductId();
    }
}