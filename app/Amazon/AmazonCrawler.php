<?php

namespace App\Amazon;

use App\Exceptions\Amazon\ProductNotFoundException;
use App\Exceptions\Amazon\SomethingWentWrongException;
use App\Jobs\Amazon\ExtractOffers;
use App\Services\ProxyManager;
use Campo\UserAgent;
use Goutte\Client;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\DomCrawler\Crawler;

class AmazonCrawler
{
    protected $asin;

    public function __construct($asin)
    {
        $this->asin = $asin;
    }

    public static function get($asin): array
    {
        return (new static($asin))->scrape();
    }

    public function scrape($extractOffers = true)
    {
        $cacheKey  = md5("amazon.com:{$this->asin}");
        $cacheTime = config('crawler.cache_time', 60);

        return cache()->remember($cacheKey, $cacheTime, function () use ($extractOffers) {
            // 1. Make Request
            $crawler = $this->requestProductPage();

            // 2. Extract Elements
            if (static::notOk($crawler)) {
                Redis::incr('crawler:amazon:fails');

                throw new SomethingWentWrongException($crawler);
            }

            if (static::isPageNotFound($crawler)) {
                Redis::incr('crawler:amazon:not_found');

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
            $offers = $extractOffers ? ExtractOffers::dispatchNow($this->asin) : [];
            if ($bestOffer = $this->bestOffer($offers)) {
                $prime = $bestOffer['prime'];
                $price = $bestOffer['price'];
            } else {
                $price = $prime = null;
            }

            $attributes = null;

            // 3. Return Data
            return [
                'processor'   => self::class,
                'asin'        => $id,
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

    public function bestOffer($offers)
    {
        if ( ! $offers instanceof Collection) {
            $offers = collect($offers);
        }

        return $offers->sortBy('price')->first();
    }

    public static function client(): Client
    {
        $guzzle = static::guzzle();

        $client = new Client;
        $client->setClient($guzzle);

        return $client;
    }

    public static function guzzle()
    {
        $config = [
            'timeout' => 60,
            'headers' => [
                'User-Agent' => static::userAgent(),
            ],
        ];

        if (config('crawler.use_proxy', false)) {
            $config['proxy'] = ProxyManager::takeOne();
        }

        return new Guzzle($config);
    }

    protected function getProductUrl(): string
    {
        return "https://www.amazon.com/dp/{$this->asin}";
    }

    public static function notOk(Crawler $crawler): bool
    {
        return ! static::isOk($crawler);
    }

    public static function isOk(Crawler $crawler): bool
    {
        if ( ! $crawler->count()) {
            return false;
        }

        $titleEl = $crawler->filter('title');

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

    public static function isPageNotFound(Crawler $crawler): bool
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

    protected function requestProductPage()
    {
        return static::getAmazonPage($this->getProductUrl());
    }

    public static function getAmazonPage(string $url): Crawler
    {
        Redis::incr('crawler:amazon:requests');

        return AmazonCrawler::client()->request(Request::METHOD_GET, $url);
    }

    public static function userAgent()
    {
        return 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36';
    }
}