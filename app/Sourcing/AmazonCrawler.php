<?php

namespace App\Sourcing;

use App\Exceptions\Amazon\ProductNotFoundException;
use App\Exceptions\Amazon\SomethingWentWrongException;
use Goutte\Client;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

class AmazonCrawler
{
    protected $asin;

    public function __construct($asin)
    {
        $this->asin = $asin;
    }

    public static function get($id): array
    {
        return (new static($id))->scrape();
    }

    public function scrape()
    {
        $cacheKey  = md5("amazon.com:{$this->asin}");
        $cacheTime = config('crawler.cache_time');

        return cache()->remember($cacheKey, $cacheTime, function () {
            // 1. Make Request
            $crawler = $this->client()->request(
                'GET',
                $this->getProductUrl()
            );

            // 2. Extract Elements
            if ( ! $this->isOk($crawler)) {
                throw new SomethingWentWrongException($crawler);
            }

            if ($this->isPageNotFound($crawler)) {
                throw new ProductNotFoundException($crawler);
            }

            $id = $this->asin;
//            $price       = $this->extractPrice($crawler); // Buy Box Price
            $title       = $this->extractTitle($crawler);
            $description = $this->extractDescription($crawler);
            $available   = $this->extractAvailability($crawler);
            $images      = $this->extractImages($crawler);
            $features    = $this->extractFeatures($crawler);

            // Get Offers Available: Only Prime + New
            $offerExtractors = OfferListingExtractor::make($this->asin);
            $offers          = $offerExtractors->offers();
            $bestOffer       = $offerExtractors->bestOffer();

            $prime      = $bestOffer['prime'];
            $price      = $bestOffer['price'];
            $attributes = null;

            // 3. Return Data
            return [
                'processor'   => self::class,
                'id'          => $id,
                'title'       => $title,
                'price'       => $price,
                'prime'       => $prime,
                'description' => $description,
                'available'   => $available,
                'images'      => $images,
                'features'    => $features,
                'attributes'  => $attributes,
                'offers'      => $offers,
            ];
        });
    }

    public static function client(): Client
    {
        $userAgent = config('crawler.user_agent');
        $proxies   = config('crawler.proxies');
        // $cacheTime = 60;

        $config = [
            'timeout' => 60,
            'headers' => [
                'User-Agent' => $userAgent,
            ],
        ];

        if ($proxies) {
            $config['proxy'] = array_random($proxies);
        }

        $guzzle = new Guzzle($config);

        $client = new Client;
        $client->setClient($guzzle);
        $client->setHeader('User-Agent', $userAgent);

        return $client;
    }

    protected function getProductUrl(): string
    {
        return "https://www.amazon.com/dp/{$this->asin}";
    }

    protected function isOk(Crawler $crawler): bool
    {
        if ( ! $crawler->count()) {
            return false;
        }

        $titleEl = $crawler->filter('head > title');

        if ( ! $titleEl->count()) {
            return false;
        }

        $pageTitle = trim($titleEl->text());

        return $pageTitle !== 'Sorry! Something went wrong!';
    }

    protected function extractPrice(Crawler $crawler)
    {
        $possibleSelectors = [
            '#priceblock_ourprice',
            '#priceblock_saleprice',
        ];

        foreach ($possibleSelectors as $selector) {
            $element = $crawler->filter($selector);

            if ($element->count()) {
                return (double)str_replace('$', '', $element->text());
            }
        }

        return null;
    }

    protected function extractTitle(Crawler $crawler)
    {
        $element = $crawler->filter('#productTitle');

        if ( ! $element->count()) {
            return null;
        }

        return trim($element->text());
    }

    protected function extractDescription(Crawler $crawler)
    {
        $element = $crawler->filter('#productDescription');

        if ( ! $element->count()) {
            return null;
        }

        return trim($element->text());
    }

    protected function extractAvailability(Crawler $crawler)
    {
        $element = $crawler->filter('#availability');

        if ( ! $element->count()) {
            return null;
        }

        $availability = trim($element->text());

        return $availability === 'In Stock.' && $availability !== 'Currently unavailable.';
    }

    protected function extractFeatures(Crawler $crawler)
    {
        $wrapper = $crawler->filter('#feature-bullets');

        if ( ! $wrapper->count()) {
            return null;
        }

        $elements = $wrapper->filter('li>span.a-list-item');

        if ( ! $elements->count()) {
            return null;
        }

        $features = collect($elements)->map(function (\DOMElement $element) {
            return trim($element->textContent);
        });

        return $features->toArray();
    }

    protected function extractImages(Crawler $crawler)
    {
        try {
            $html = $crawler->html();

            $html = explode('\'colorImages\': { \'initial\': ', $html)[1];

            $html = explode('\'colorToAsin\'', $html)[0];

            $html = str_replace("},\n", '', $html);

            $imageSet = json_decode($html, true);

            $images = $this->highestResImages($imageSet)->all();

            return $images;
        } catch (\Exception $exception) {
            return null;
        }
    }

    protected function highestResImages($imageSet): Collection
    {
        return collect($imageSet)->map(function ($image) {
            if (@$image['hiRes']) {
                return $image['hiRes'];
            }

            if (@$image['large']) {
                return $image['large'];
            }

            if (@$image['thumb']) {
                return $image['thumb'];
            }

            return null;
        });
    }

    protected function isPageNotFound(Crawler $crawler): bool
    {
        $pageTitle = trim($crawler->filter('head > title')->text());

        return $pageTitle == 'Page Not Found';
    }

    protected function getOfferListingLink()
    {
        return "https://www.amazon.com/gp/offer-listing/{$this->asin}/?f_new=true&f_primeEligible=true";
    }

    protected function extractOffers()
    {
        $extractor = OfferListingExtractor::make($this->asin);

        return $extractor->offers();
    }
}