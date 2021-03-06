<?php

namespace App\Http\Controllers;

use App\Account;
use App\Sourcing\AmazonCom;
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
        $user = $this->resolveCurrentUser($request)->load('templates');

        $this->validate($request, [
            'source'     => 'required|in:amazon.com',
            'product_id' => 'required',
            'account'    => [
                'required',
                'exists:accounts,username',
                function ($attribute, $value, $fail) use ($request, $user) {
                    if (Account::find($value)['user_id'] !== $user['id']) {
                        return $fail($attribute . ' is invalid.');
                    }
                },
            ],
        ]);

        $account             = Account::find($request['account']);
        $product             = (new AmazonCom($request['product_id']))->scrape();
        $suggestedCategories = $account->suggestCategory($product['title']);
        $templates           = $user['itemDescriptionTemplates'];

        return view(
            'builder.customize',
            compact('account', 'product', 'suggestedCategories', 'templates')
        );
    }
}
