<?php

namespace App\Http\Controllers\Account;

use App\Account;
use App\Exceptions\CanNotFetchProductInformation;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListAnItemRequest;
use App\Sourcing\Amazon\AmazonProduct;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function addItem(ListAnItemRequest $request, Account $account)
    {
        $this->authorize('listing', $account);

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
            $asin = $request['product_id'];

            $amazonProduct = new AmazonProduct($asin);

            $fields = $amazonProduct->fetch();

            return array_merge($fields, ['listed_on' => $amazonProduct->listedOnAccountsOfUser($request->user())]);
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
