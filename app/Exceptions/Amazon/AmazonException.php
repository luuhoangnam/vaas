<?php

namespace App\Exceptions\Amazon;

use Symfony\Component\DomCrawler\Crawler;

abstract class AmazonException extends \Exception
{
    protected $crawler;

    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function getCrawler(): Crawler
    {
        return $this->crawler;
    }
}