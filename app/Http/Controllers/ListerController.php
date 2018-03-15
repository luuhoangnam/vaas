<?php

namespace App\Http\Controllers;

use App\Account;
use App\Sourcing\AmazonCom;
use Illuminate\Http\Request;

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
        $product             = AmazonCom::get($request['product_id']);
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
                'id' => $categoryID,
                'name' => $category['Category']['CategoryName'],
                'breadcrumb' => $categoryBreadcrumb,
                'percent' => $percentFound,
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
}
