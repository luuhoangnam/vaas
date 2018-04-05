<?php

namespace App\Sourcing\Amazon;

use App\Exceptions\Amazon\SomethingWentWrongException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\DomCrawler\Crawler;

class OfferListingExtractor
{
    protected $crawler;

    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public static function make($asin, $prime = true, $new = true, $freeShipping = true): OfferListingExtractor
    {
        $url = static::url($asin, $prime, $new, $freeShipping);

        $crawler = AmazonCrawler::getAmazonPage($url);

        if (AmazonCrawler::notOk($crawler)) {
            Redis::incr('crawler:amazon:fails');

            throw new SomethingWentWrongException($crawler);
        }

        return new static($crawler);
    }

    public static function url($asin, $prime = true, $new = true, $freeShipping = true)
    {
        $filters = [];

        $prime ? $filters[] = "f_primeEligible=true" : null;
        $new ? $filters[] = "f_new=true" : null;
        $freeShipping ? $filters[] = "f_freeShipping=true" : null;

        return "https://www.amazon.com/gp/offer-listing/{$asin}/ref=olp_f_freeShipping?" . join('&', $filters);
    }

    public function offers()
    {
        $container = $this->crawler->filter('#olpOfferList');

        if ( ! $container->count()) {
            return null;
        }

        $rows = $container->filter('.olpOffer');

        if ( ! $container->count()) {
            return [];
        }

        return $rows->each(function (Crawler $row) {

            # Price
            $priceEl = $row->filter('.olpOfferPrice');
            $price   = $priceEl->count() ? (double)str_replace('$', '', trim($priceEl->text())) : null;

            # Seller Information
            $sellerEl = $row->filter('.olpSellerName');
            if ($sellerEl->count()) {

                if ($sellerEl->filter('[alt="Amazon.com"]')->count()) {
                    $seller = 'Amazon.com';
                } else {
                    $seller = trim($sellerEl->text());
                }
            } else {
                $seller = null;
            }

            return [
                'prime'   => true,
                'new'     => true,
                'price'   => $price,
                'seller'  => $seller,
                'has_tax' => $seller === 'Amazon.com',
            ];
        });
    }

    public function bestOffer()
    {
        return collect($this->offers())->sortBy('price')->first();
    }

    public static function bestOfferWithTax($offers = null, $taxRate = 0.09)
    {
        if ( ! $offers instanceof Collection) {
            $offers = collect($offers);
        }

        return $offers->sortBy(function (array $offer) use ($taxRate) {
            return $offer['seller'] === 'Amazon.com' ? $offer['price'] * $taxRate : $offer['price'];
        })->first();
    }
}