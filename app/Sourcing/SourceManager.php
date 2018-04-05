<?php

namespace App\Sourcing;

use App\Sourcing\Suppliers\Amazon;
use App\Sourcing\Suppliers\Amazon\APIClient;
use InvalidArgumentException;

class SourceManager
{
    protected $app;
    protected $suppliers = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function supplier($name = null)
    {
        $name = $name ?: $this->getDefaultSupplier();

        return $this->suppliers[$name] = $this->get($name);
    }

    protected function getDefaultSupplier()
    {
        return $this->app['config']['sourcing.default'];
    }

    protected function get($name)
    {
        return $this->stores[$name] ?? $this->resolve($name);
    }

    protected function getConfig($name)
    {
        return $this->app['config']["sourcing.suppliers.{$name}"];
    }

    protected function resolve($name): Supplier
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Supplier [{$name}] is not defined.");
        }

        $driverMethod = 'create' . ucfirst($config['driver']) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

    protected function createAmazonDriver()
    {
        return new Amazon($this->app[APIClient::class]);
    }

    public function __call($method, $parameters)
    {
        return $this->supplier()->{$method}(...$parameters);
    }
}