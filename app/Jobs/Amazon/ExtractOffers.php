<?php

namespace App\Jobs\Amazon;

use App\Exceptions\Amazon\SomethingWentWrongException;
use App\Product;
use App\Sourcing\OfferListingExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtractOffers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    protected $asin;
    protected $prime;
    protected $new;
    protected $freeShipping;

    public function __construct($asin, $prime = true, $new = true, $freeShipping = true)
    {
        $this->asin         = $asin;
        $this->prime        = $prime;
        $this->new          = $new;
        $this->freeShipping = $freeShipping;
    }

    public function handle(): array
    {
        try {
            $offers = OfferListingExtractor::make($this->asin, $this->prime, $this->new, $this->freeShipping)->offers();

            Product::sync(['asin' => $this->asin, 'offers' => $offers]);

            return $offers;
        } catch (SomethingWentWrongException $exception) {
            $this->release(3 ^ $this->attempts());

            Redis::incr('crawler:amazon:retries');

            throw $exception;
        }
    }
}
