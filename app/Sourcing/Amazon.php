<?php

namespace App\Sourcing;

use Illuminate\Support\Collection;
use Revolution\Amazon\ProductAdvertising\AmazonClient;

class Amazon
{
    public static function inspect($asin)
    {
        $api = static::amazonProductAdvertisingApi();

        $response = $api->item($asin);

        if (key_exists('Errors', $response['Items']['Request'])) {
            $errors = $response['Items']['Request']['Errors'];
            $error  = array_first($errors);

            if ($error['Code'] === 'AWS.InvalidParameterValue') {
                throw new \InvalidArgumentException($error['Message']);
            }
        }

        $item = $response['Items']['Item'];

        if ( ! key_exists('Offer', $item['Offers'])) {
            throw new \InvalidArgumentException($error['Message']);
//            return AmazonCom::get($id); // Out of Stock
        }

        $offer   = $item['Offers']['Offer'];
        $listing = $offer['OfferListing'];

        $title       = $item['ItemAttributes']['Title'];
        $description = $item['EditorialReviews']['EditorialReview']['Content'];
        $price       = (double)$listing['Price']['Amount'] / 100;
        $available   = $listing['AvailabilityAttributes']['AvailabilityType'] === 'now';
        $images      = static::getImagesForApi($item['ImageSets']['ImageSet'])->all();

        $attributes = static::castsAttribute(array_only($item['ItemAttributes'], [
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
        ]));

        $prime = (bool)$listing['IsEligibleForPrime'];

        $features = $item['ItemAttributes']['Feature'];

        return compact(
            'id', 'title', 'description',
            'price', 'available', 'prime',
            'images', 'features',
            'attributes'
        );
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