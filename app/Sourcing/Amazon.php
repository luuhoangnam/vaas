<?php

namespace App\Sourcing;

use App\Exceptions\Amazon\ProductNotFoundException;
use App\Exceptions\Amazon\SomethingWentWrongException;
use Goutte\Client;
use GuzzleHttp\Client as Guzzle;
use Symfony\Component\DomCrawler\Crawler;

abstract class Amazon
{
    protected $productId;

    public function __construct($productId)
    {
        $this->productId = $productId;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public static function get($id): array
    {
        return (new static($id))->scrape();
    }

    public function scrape()
    {
        $cacheKey  = md5("amazon.com:{$this->getProductId()}");
        $cacheTime = config('crawler.cache_time');

        return cache()->remember($cacheKey, $cacheTime, function () {
            // 1. Make Request
            $crawler = $this->client()->request(
                'GET',
                $this->getProductUrl()
            );

            // 2. Extract Elements
            $ok           = $this->isOk($crawler);
            $pageNotFound = $this->isPageNotFound($crawler);

            if ( ! $ok) {
                throw new SomethingWentWrongException($crawler);
            } elseif ($pageNotFound) {
                throw new ProductNotFoundException($crawler);
            }

            $id          = $this->getProductId();
            $price       = $this->extractPrice($crawler);
            $title       = $this->extractTitle($crawler);
            $description = $this->extractDescription($crawler);
            $available   = $this->extractAvailability($crawler);
            $images      = $this->extractImages($crawler);
            $features    = $this->extractFeatures($crawler);
            $attributes  = $this->extractAttributes($crawler);

            // 3. Return Data
            return compact('id', 'title', 'description', 'price', 'available', 'images', 'features', 'attributes');
        });
    }

    protected function client(): Client
    {
        $userAgent = config('crawler.user_agent');
        $cacheTime = 60;

        $guzzle = new Guzzle([
            'timeout' => 60,
            'headers' => [
                'User-Agent' => $userAgent,
            ],
        ]);

        $client = new Client;
        $client->setClient($guzzle);
        $client->setHeader('User-Agent', $userAgent);

        return $client;
    }

    abstract protected function getProductUrl(): string;

    protected function isOk(Crawler $crawler): bool
    {
        $pageTitle = trim($crawler->filter('head > title')->text());

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
            return [];
        }

        $elements = $wrapper->filter('li>span.a-list-item');

        if ( ! $elements->count()) {
            return [];
        }

        $features = collect($elements)->map(function (\DOMElement $element) {
            return trim($element->textContent);
        });

        return $features->toArray();

    }

    protected function extractImages(Crawler $crawler)
    {
        $element = $crawler->filter('#imgTagWrapperId img');

        if ( ! $element->count()) {
            return [];
        }

        $object = json_decode($element->attr('data-a-dynamic-image'), true);

        return array_keys($object);
    }

    protected function extractAttributes(Crawler $crawler)
    {
        return [];

        $wrapper = $crawler->filter('#detail-bullets');

        if ( ! $wrapper->count()) {
            return [];
        }

        $elements = $wrapper->filter('table .content ul li');

        foreach ($elements as $element) {
            // TODO Extract attribute
        }

        return [];
    }

    protected function isPageNotFound(Crawler $crawler): bool
    {
        $pageTitle = trim($crawler->filter('head > title')->text());

        return $pageTitle == 'Page Not Found';
    }
}