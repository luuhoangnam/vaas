<?php

namespace App\Http\Controllers;

use App\Cashback\AmazonAssociates;
use App\Cashback\AmazonAssociatesCashbackRateResolver;
use App\Exceptions\InvalidAmazonAssociatesItemException;
use App\Exceptions\NonAffiliatableException;
use App\Sourcing\AmazonProduct;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function redirect(Request $request, $asin)
    {
        try {
            $url = (new AmazonAssociates)->link($asin);

            return view('intermediates.amazon', compact('asin', 'url'));
        } catch (InvalidAmazonAssociatesItemException $exception) {
            $error = 'Invalid Amazon ASIN';

            return view('intermediates.amazon', compact('error'));
        } catch (NonAffiliatableException $exception) {
            $error = 'Affiliate Blocked Product';

            return view('intermediates.amazon', compact('error'));
        }
    }
}
