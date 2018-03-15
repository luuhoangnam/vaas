<?php

namespace App\Http\Controllers;

use App\Account;
use App\Sourcing\AmazonCom;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Revolution\Amazon\ProductAdvertising\AmazonClient;

class ListerController extends AuthRequiredController
{
    // Step #1
    public function start(Request $request)
    {
        // Choose account, source, source item
        $accounts = $request->user()['accounts'];
        $sources  = ['amazon.com'];

        return view('lister.start', compact('accounts', 'sources'));
    }

    // Step #2
    public function customize(Request $request)
    {
        # Validate the request
        $this->validate($request, [
            'account'    => 'required|exists:accounts,username',
            'source'     => 'required|in:amazon.com',
            'product_id' => 'required',
        ]);

        # Get necessary information to display
        $user                = $this->resolveCurrentUser($request);
        $account             = Account::find($request['account']);
        $product             = $this->product($request['product_id']);
        $suggestedCategories = $account->suggestCategory($product['title']);
        $previewUrl          = $this->previewUrl('vaas', $product);
        $profiles            = collect($account->sellerProfiles())->groupBy('ProfileType');
        $description         = view('lister.templates.vaas', [
            'title'       => $product['title'],
            'description' => $product['description'],
            'image'       => array_first($product['images']),
            'features'    => $product['features'],
            'editor'      => true,
        ]);

        $categories = [];

        foreach ($suggestedCategories as $category) {
            $categoryID         = (int)$category['Category']['CategoryID'];
            $categoryBreadcrumb = join(' > ', $category['Category']['CategoryParentName']);
            $percentFound       = $category['PercentItemFound'];

            $categories[] = [
                'id'         => $categoryID,
                'name'       => $category['Category']['CategoryName'],
                'breadcrumb' => $categoryBreadcrumb,
                'percent'    => $percentFound,
            ];
        }

        return view(
            'lister.customize',
            compact(
                'user', 'account', 'product',
                'previewUrl', 'suggestedCategories', 'profiles',
                'description', 'categories'
            )
        );
    }

    public function preview(Request $request, $template)
    {
        if ( ! file_exists(resource_path("views/lister/templates/{$template}.blade.php"))) {
            return abort(404);
        }

        $this->validate($request, [
            'title'       => 'required',
            'description' => 'required',
            'image'       => 'required|url',
            'features'    => 'required|array',
        ]);

        return view("lister.templates.{$template}", $request->only('title', 'description', 'image', 'features'));
    }

    // Step #3
    public function submit(Request $request)
    {
        dd($request->all());
    }

    protected function previewUrl($template, array $product)
    {
        $data = [
            'title'       => urlencode($product['title']),
            'description' => urlencode($product['description']),
            'image'       => urlencode($product['images'][0]),
            'features'    => $product['features'],
        ];

        $features = [];
        foreach ($data['features'] as $feature) {
            $features[] = 'features[]=' . urlencode($feature);
        }

        $params = [
            "title={$data['title']}",
            "description={$data['description']}",
            "image={$data['image']}",
            join('&', $features),
        ];

        return route('lister.preview', [$template]) . '?' . join('&', $params);
    }

    protected function product($id)
    {
        $cacheKey = md5(serialize([
            'getProduct' => ['type' => 'amazon', 'id' => $id],
        ]));

        $cacheTime = 60; // 1 Hour

        return cache()->remember($cacheKey, $cacheTime, function () use ($id) {
            $api = $this->amazonProductAdvertisingApi();

            $response = $api->item($id);

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
            $images      = $this->getImagesForApi($item['ImageSets']['ImageSet'])->all();

            $attributes = array_only($item['ItemAttributes'], [
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
            ]);

            $features = $attributes['Feature'];

            $prime = $listing['IsEligibleForPrime'];

            return compact(
                'id', 'title', 'description', 'price', 'available', 'images', 'features', 'attributes', 'prime'
            );
        });
    }

    protected function getImagesForApi($imageSet): Collection
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

    protected function amazonProductAdvertisingApi(): AmazonClient
    {
        return app(AmazonClient::class);
    }
}
