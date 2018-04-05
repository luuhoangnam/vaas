<?php

namespace App\Sourcing\Suppliers;

use App\Sourcing\Exceptions\AmazonAPIException;
use App\Sourcing\Product;
use App\Sourcing\Supplier;
use App\Sourcing\Suppliers\Amazon\Client;

class Amazon implements Supplier
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $asin
     *
     * @return Product
     * @throws AmazonAPIException
     */
    public function get($asin): Product
    {
        return $this->lookup($asin, 'ASIN');
    }

    /**
     * @param mixed  $term
     * @param string $mode
     *
     * @return Product
     * @throws AmazonAPIException
     */
    public function lookup($term, $mode): Product
    {
        $data = $this->client->get($term, $mode);

        $data['supplier'] = $this;
        $data['attributes'] = $this->filterAttributes($data['attributes']);

        return new Product($data);
    }

    protected function filterAttributes($attributes)
    {
        $keep = ['Brand', 'Color', 'EAN', 'PartNumber', 'Label', 'PackageQuantity', 'MPN', 'Model', 'Size', 'UPC'];

        return array_only($attributes, $keep);
    }

    public function otherFees($price)
    {
        $giftcardRate = 0.0375;

        return $price * $giftcardRate;
    }
}