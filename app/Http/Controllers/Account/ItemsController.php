<?php

namespace App\Http\Controllers\Account;

use App\Account;
use App\Exceptions\CanNotFetchProductInformation;
use App\Sourcing\AmazonProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class ItemsController extends Controller
{
    public function addItem(Request $request, Account $account)
    {
        $this->validate($request, [
            'title'               => 'required',
            'description'         => 'required|max:500000',
            'condition_id'        => 'required|integer',
            'quantity'            => 'required|integer',
            'sku'                 => '',
            'price'               => 'required|numeric',
            'category_id'         => 'required|string',
            'payment_profile_id'  => 'required|integer',
            'shipping_profile_id' => 'required|integer',
            'return_profile_id'   => 'required|integer',
            'pictures'            => 'required|array|min:1',
            'pictures.*'          => 'url',
            // Attrs
            'upc'                 => '',
            'mpn'                 => '',
        ]);

        $response = $account->addItem($request->all());

        return $response->toArray();
    }

    public function suggestCategory(Request $request, Account $account)
    {
        $this->validate($request, [
            'query' => 'required|between:2,80',
        ]);

        return $account->suggestCategory($request['query']);
    }

    public function sellerProfiles(Request $request, Account $account)
    {
        return $account->sellerProfiles();
    }

    public function allowedConditions(Request $request, Account $account, $categoryId)
    {
        return $account->categoryFeatures($categoryId);
    }

    public function inspectSourceProduct(Request $request)
    {
        $this->validate($request, [
            'source'     => 'required|in:amazon.com',
            'product_id' => 'required',
        ]);

        try {
            return (new AmazonProduct($request['product_id']))->fetch();
        } catch (CanNotFetchProductInformation $exception) {
            return response()->json([
                'message' => 'Can not fetch information for this product id',
                'errors'  => [
                    'product_id' => 'Invalid Product ID',
                ],
            ], 422);
        }
    }
}
