<?php

namespace App\Sourcing;

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
        $filters = [];

        $prime ? $filters[] = "f_primeEligible=true" : null;
        $new ? $filters[] = "f_new=true" : null;
        $freeShipping ? $filters[] = "f_freeShipping=true" : null;

        $url = "https://www.amazon.com/gp/offer-listing/{$asin}/ref=olp_f_freeShipping?" . join('&', $filters);

        $crawler = AmazonCrawler::client()->request('GET', $url);

        return new static($crawler);
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
                'prime'  => true,
                'new'    => true,
                'price'  => $price,
                'seller' => $seller,
            ];
        });
    }

    public function bestOffer()
    {
        return collect($this->offers())->sortBy('price')->first();
    }
}