<?php

namespace App\Sourcing;

use App\Exceptions\AmazonException;
use Illuminate\Support\Collection;
use Revolution\Amazon\ProductAdvertising\AmazonClient;

class Amazon
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
    ];

    public static function inspect($asin)
    {
        $api = static::amazonProductAdvertisingApi();

        $response = $api->item($asin);

        if (key_exists('Errors', $response['Items']['Request'])) {
            $errors = $response['Items']['Request']['Errors'];
            $error  = array_first($errors);

            if ($error['Code'] === 'AWS.InvalidParameterValue') {
                throw new AmazonException($error['Message'], $error['Code']);
            }
        }

        $item        = $response['Items']['Item'];
        $title       = $item['ItemAttributes']['Title'];
        $description = $item['EditorialReviews']['EditorialReview']['Content'];

        $images     = static::getImagesForApi($item['ImageSets']['ImageSet'])->all();
        $features   = $item['ItemAttributes']['Feature'];
        $attributes = static::castsAttribute(array_only($item['ItemAttributes'], static::$keepAttributes));

        # Currently Unavailable
        if ( ! key_exists('Offer', $item['Offers'])) {
            return [
                'id'          => $asin,
                'title'       => $title,
                'description' => $description,
                'price'       => null,
                'available'   => false,
                'prime'       => null,
                'images'      => $images,
                'features'    => $features,
                'attributes'  => $attributes,
            ];
        }

        # Offer Specific
        $offer   = $item['Offers']['Offer'];
        $listing = $offer['OfferListing'];

        $price     = (double)$listing['Price']['Amount'] / 100;
        $available = $listing['AvailabilityAttributes']['AvailabilityType'] === 'now';
        $prime     = (bool)$listing['IsEligibleForPrime'];

        // TODO Get Best Offer (New + Prime)

        return [
            'id'          => $asin,
            'title'       => $title,
            'description' => $description,
            'price'       => $price,
            'available'   => $available,
            'prime'       => $prime,
            'images'      => $images,
            'features'    => $features,
            'attributes'  => $attributes,
        ];
    }

    protected static function getImagesForApi($imageSet): Collection
    {
        return collect($imageSet)->map(function ($image) {
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
        });
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
}