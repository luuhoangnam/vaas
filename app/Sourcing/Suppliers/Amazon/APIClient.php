<?php

namespace App\Sourcing\Suppliers\Amazon;

use App\Sourcing\Exceptions\AmazonAPIException;
use Illuminate\Support\Collection;
use Revolution\Amazon\ProductAdvertising\AmazonClient;

class APIClient implements Client
{
    protected $amazon;

    public function __construct(AmazonClient $amazon)
    {
        $this->amazon = $amazon;
    }

    /**
     * @param mixed  $id
     * @param string $mode
     *
     * @return array
     * @throws AmazonAPIException
     */
    public function get($id, $mode)
    {
        $item = $this->getItem($id, $mode);

        $asin = $item['ASIN'];
        $title = $item['ItemAttributes']['Title'];
        $description = $this->description($item);

        if ($description == $id) {
            $description = '';
        }

        $images = @$item['ImageSets']['ImageSet'] ? $this->getImagesForApi($item['ImageSets']['ImageSet'])->all() : [];
        $features = @$item['ItemAttributes']['Feature'] ?: [];
        $attributes = $this->castsAttribute($item['ItemAttributes']);
        $upc = array_get($attributes, 'UPC');
        $ean = array_get($attributes, 'EAN');

        # Currently Unavailable
        if ( ! key_exists('Offer', $item['Offers'])) {

            return [
                'asin'        => $asin,
                'upc'         => $upc,
                'ean'         => $ean,
                'title'       => $title,
                'images'      => $images,
                'description' => $description,
                'attributes'  => $attributes,
                'price'       => null,
                'available'   => false,
                'custom'      => [
                    'prime'    => null,
                    'features' => $features,
                ],
            ];
        }

        # Offer Specific
        $offer = $item['Offers']['Offer'];
        $listing = $offer['OfferListing'];

        $price = $this->price($listing);
        $available = $listing['AvailabilityAttributes']['AvailabilityType'] === 'now';
        $prime = (bool)$listing['IsEligibleForPrime'];

        # Extract Offers on Demand
        return [
            'asin'        => $asin,
            'upc'         => $upc,
            'ean'         => $ean,
            'title'       => $title,
            'description' => $description,
            'price'       => $price,
            'available'   => $available,
            'attributes'  => $attributes,
            'images'      => $images,
            'custom'      => [
                'prime'    => $prime,
                'features' => $features,
            ],
        ];
    }

    /**
     * @param mixed  $id
     * @param string $mode
     *
     * @return array
     * @throws AmazonAPIException
     */
    protected function getItem($id, $mode)
    {
        $response = $this->request($id, $mode);

        $itemHolder = $response['Items']['Item'];

        return is_assoc($itemHolder) ? $itemHolder : array_first($itemHolder);
    }

    /**
     * @param mixed  $id
     * @param string $mode
     *
     * @return array
     * @throws AmazonAPIException
     */
    protected function request($id, $mode)
    {
        $response = $this->amazon->setIdType($mode)->item($id);

        if (key_exists('Errors', $response['Items']['Request'])) {
            $errors = $response['Items']['Request']['Errors'];
            $error = array_first($errors);

            throw new AmazonAPIException($error['Message'], $error['Code']);
        }

        return $response;
    }

    protected function description($item): string
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

    protected function chooseHighestQualityImage($image)
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

    protected function price($listing)
    {
        if (key_exists('SalePrice', $listing)) {
            return (double)$listing['SalePrice']['Amount'] / 100;
        }

        return (double)$listing['Price']['Amount'] / 100;
    }

    protected function getImagesForApi($imageSet): Collection
    {
        $keys = array_keys($imageSet);

        $intersect = array_intersect(
            ['HiResImage', 'LargeImage', 'MediumImage', 'TinyImage', 'ThumbnailImage', 'SmallImage', 'SwatchImage'],
            $keys
        );

        if ($intersect) {
            // Single Image
            return new Collection([$this->chooseHighestQualityImage($imageSet)]);
        }

        return collect($imageSet)->map(function ($image) {
            return $this->chooseHighestQualityImage($image);
        });
    }

    protected function castsAttribute(array $attributes): array
    {
        $ints = ['EAN', 'MPN', 'PackageQuantity', 'UPC'];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $ints)) {
                $attributes[$key] = (int)$value;
            }
        }

        return $attributes;
    }
}