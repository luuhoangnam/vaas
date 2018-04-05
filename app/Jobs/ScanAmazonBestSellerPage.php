<?php

namespace App\Jobs;

use App\Product;
use App\Amazon\AmazonCrawler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

class ScanAmazonBestSellerPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function handle()
    {
        $crawler = AmazonCrawler::getAmazonPage($this->url);

        if (AmazonCrawler::notOk($crawler)) {
            Redis::incr('crawler:amazon:fails');

            return $this->tryAgain();
        }

        #1. All Products on This Page
        $this->trackProducts($crawler);

        #2. Queue Next Page
        if ($this->nextPage()) {
            self::dispatch($this->nextPageUrl())->delay(10);
        }

//            #3. Queue Scan Sub Nodes
//            $this->queueSubNodes($crawler);

    }

    public function tags()
    {
        return [
            'amazon',
            'best-seller',
            'product',
            'crawler',
            "url:{$this->url}",
        ];
    }

    protected function bestSellers(Crawler $crawler): array
    {
        return $crawler->filter('.zg_itemWrapper')->each(function (Crawler $el) {
            $p13nASINJson = $el->filter('.p13n-asin')->attr('data-p13n-asin-metadata');

            $p13nASIN = json_decode($p13nASINJson, true);

            return $p13nASIN['asin'];
        });
    }

    protected function currentPage()
    {
        $query = parse_url($this->url, PHP_URL_QUERY);

        if (is_null($query)) {
            return 1;
        }

        $params = collect(explode('&', $query))->map(function ($param) {
            $parts = explode('=', $param);

            return ['name' => $parts[0], 'value' => $parts[1]];
        });

        $pageParam = $params->where('name', 'pg')->first();

        return (int)$pageParam['value'];
    }

    protected function nextPage()
    {
        $current = $this->currentPage();

        return $current < 5 ? $current + 1 : null;
    }

    public function nextPageUrl()
    {
        $scheme = parse_url($this->url, PHP_URL_SCHEME);
        $host   = parse_url($this->url, PHP_URL_HOST);
        $path   = parse_url($this->url, PHP_URL_PATH);

        return "{$scheme}://{$host}/{$path}?pg=" . $this->nextPage();
    }

    protected function queueSubNodes(Crawler $crawler)
    {
        if ($this->currentPage() === 1) {
            collect($this->getSubNodes($crawler))->each(function ($node) {
                self::dispatch($node['url'])->delay(rand(10, 60));
            });
        }
    }

    protected function getSubNodes(Crawler $crawler)
    {
        $selectedNodeEl = $crawler->filter('#zg_browseRoot .zg_selected');

        $liEl = $selectedNodeEl->parents()
                               ->eq(0);

        $ulSubNodesEl = $liEl->siblings()
                             ->eq(0);

        return $ulSubNodesEl->filter('li a')->each(function (Crawler $el) {
            return [
                'name' => $el->text(),
                'url'  => $el->attr('href'),
            ];
        });
    }

    protected function trackProducts(Crawler $crawler)
    {
        $asins = $this->bestSellers($crawler);

        foreach ($asins as $asin) {
            try {
                Product::find($asin);
            } catch (ModelNotFoundException $exception) {
                // Product not tracked yet
                SyncAmazonProduct::dispatch($asin);
            }
        }
    }

    protected function tryAgain()
    {
        if ($this->attempts() < 3) {
            $this->release(2 ^ $this->attempts()); // Try again later
        }
    }
}
