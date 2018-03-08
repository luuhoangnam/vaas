<?php

namespace App\Http\Controllers;

use App\Account;
use App\Sourcing\AmazonProduct;
use Illuminate\Http\Request;

class ListingBuilderController extends AuthRequiredController
{
    public function start(Request $request)
    {
        // Choose account, source, source item
        $accounts = $request->user()['accounts'];
        $sources  = ['amazon.com'];

        return view('builder.start', compact('accounts', 'sources'));
    }

    public function build(Request $request)
    {
        $this->validate($request, [
            'source'     => 'required|in:amazon.com',
            'product_id' => 'required',
            'account'    => [
                'required',
                'exists:accounts,username',
                function ($attribute, $value, $fail) use ($request) {
                    if (Account::find($value)['user_id'] !== $request->user()->id) {
                        return $fail($attribute . ' is invalid.');
                    }
                },
            ],
        ]);

        $account             = Account::find($request['account']);
        $product             = (new AmazonProduct($request['product_id']))->fetch();
        $suggestedCategories = $account->suggestCategory($product['title']);

        return view('builder.customize', compact('account', 'product', 'suggestedCategories'));
    }
}