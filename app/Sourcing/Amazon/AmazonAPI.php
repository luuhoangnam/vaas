<?php

namespace App\Sourcing\Amazon;

use App\Exceptions\Amazon\ProductAdvertisingAPIException;
use App\Jobs\Amazon\ExtractOffers;
use Illuminate\Support\Collection;
use Revolution\Amazon\ProductAdvertising\AmazonClient;

class AmazonAPI
{
    protected static $keepAttributes = [
        'Brand',
        'Color',
        'EAN',
        'PartNumber',
        'Manufacturer',
        'Label',
        'PackageQuantity',
        'MPN',
        'Model',
        'ProductGroup',
        'ProductTypeName',
        'Size',
        'Publisher',
        'UPC',
    ];

    public static function ean($ean)
    {
        return static::item($ean, AmazonIdMode::EAN);
    }

    public static function ibsn($ibsn)
    {
        return static::item($ibsn, AmazonIdMode::ISBN);
    }

    public static function upc($upc)
    {
        return static::item($upc, AmazonIdMode::UPC);
    }

    public static function asin($asin)
    {
        return static::item($asin, AmazonIdMode::ASIN);
    }

    public static function item($id, $mode = AmazonIdMode::ASIN)
    {
        if ( ! AmazonIdMode::isValid($mode)) {
            throw new \InvalidArgumentException('Can not accept this amazon ID type');
        }

        return static::amazonProductAdvertisingApi()->setIdType($mode)->item($id);
    }

    public static function inspect($id, $includeOffers = false, $mode = AmazonIdMode::ASIN)
    {
        $response = static::item($id, $mode);

        if (key_exists('Errors', $response['Items']['Request'])) {
            $errors = $response['Items']['Request']['Errors'];
            $error  = array_first($errors);

            throw new ProductAdvertisingAPIException($error['Message'], $error['Code']);
        }

        $itemHolder = $response['Items']['Item'];

        $item        = is_assoc($itemHolder) ? $itemHolder : array_first($itemHolder);
        $asin        = $item['ASIN'];
        $title       = $item['ItemAttributes']['Title'];
        $description = static::description($item);

        if ($description == $id) {
            $description = '';
        }

        $images     = @$item['ImageSets']['ImageSet'] ? static::getImagesForApi($item['ImageSets']['ImageSet'])->all() : [];
        $features   = @$item['ItemAttributes']['Feature'] ?: [];
        $attributes = static::castsAttribute(array_only($item['ItemAttributes'], static::$keepAttributes));

        # Currently Unavailable
        if ( ! key_exists('Offer', $item['Offers'])) {
            return [
                'asin'        => $asin,
                'title'       => $title,
                'description' => $description,
                'price'       => null,
                'available'   => false,
                'prime'       => null,
                'images'      => $images,
                'features'    => $features,
                'attributes'  => $attributes,
                'offers'      => [],
            ];
        }

        # Offer Specific
        $offer   = $item['Offers']['Offer'];
        $listing = $offer['OfferListing'];

        $price     = static::price($listing);
        $available = $listing['AvailabilityAttributes']['AvailabilityType'] === 'now';
        $prime     = (bool)$listing['IsEligibleForPrime'];

        # Extract Offers on Demand
        $offers = $includeOffers ? ExtractOffers::dispatchNow($asin) : null;

        return [
            'processor'   => self::class,
            'asin'        => $asin,
            'title'       => $title,
            'description' => $description,
            'price'       => $price,
            'available'   => $available,
            'prime'       => $prime,
            'images'      => $images,
            'features'    => $features,
            'attributes'  => $attributes,
            'offers'      => $offers,
        ];
    }

    public static function bestSellers($nodeID)
    {
        $api = static::amazonProductAdvertisingApi();

        $response = $api->browse($nodeID);
    }

    protected static function getImagesForApi($imageSet): Collection
    {
        $keys = array_keys($imageSet);

        $intersect = array_intersect(
            ['HiResImage', 'LargeImage', 'MediumImage', 'TinyImage', 'ThumbnailImage', 'SmallImage', 'SwatchImage'],
            $keys
        );

        if ($intersect) {
            // Single Image
            return collect(
                [
                    static::chooseHighestQualityImage($imageSet),
                ]
            );
        }

        return collect($imageSet)->map(function ($image) {
            return static::chooseHighestQualityImage($image);
        });
    }

    protected static function chooseHighestQualityImage($image)
    {
        if (@$image['HiResImage']) {
            return $image['HiResImage']['URL'];
        }

        if (@$image['LargeImage']) {
            return $image['LargeImage']['URL'];
        }

        if (@$image['MediumImage']) {
            return $image['MediumImage']['URL'];
        }

        if (@$image['TinyImage']) {
            return ($image['TinyImage']['URL']);
        }

        if (@$image['ThumbnailImage']) {
            return ($image['ThumbnailImage']['URL']);
        }

        if (@$image['SmallImage']) {
            return ($image['SmallImage']['URL']);
        }

        if (@$image['SwatchImage']) {
            return ($image['SwatchImage']['URL']);
        }

        return null;
    }

    protected static function amazonProductAdvertisingApi(): AmazonClient
    {
        return app(AmazonClient::class);
    }

    protected static function castsAttribute(array $attributes): array
    {
        $ints = ['EAN', 'MPN', 'PackageQuantity'];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $ints)) {
                $attributes[$key] = (int)$value;
            }
        }

        return $attributes;
    }

    protected static function price($listing)
    {
        if (key_exists('SalePrice', $listing)) {
            return (double)$listing['SalePrice']['Amount'] / 100;
        }

        return (double)$listing['Price']['Amount'] / 100;
    }

    protected static function description($item): string
    {
        if ( ! key_exists('EditorialReviews', $item) || ! key_exists('EditorialReview', $item['EditorialReviews'])) {
            return '';
        }

        $editorialReview = $item['EditorialReviews']['EditorialReview'];

        if (key_exists('Source', $editorialReview) && $editorialReview['Source'] === 'Product Description') {
            return $editorialReview['Content'];
        }

        $filtered = collect($editorialReview)->where('Source', 'Product Description');

        if ($filtered->count() == 0) {
            return '';
        }

        $description = $filtered->first();

        if ( ! key_exists('Content', $description)) {
            return '';
        }

        return $description['Content'];
    }
}