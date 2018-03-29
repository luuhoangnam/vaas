<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class LoadProxyBonanzaProxies extends Command
{
    protected $signature = 'proxy:load';
    protected $description = 'Load Proxy Bonanza Proxies List to Local';
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $proxies = new Collection;

        $this->getPackages()->each(function ($package) use ($proxies) {
            $this->getProxies($package['id'])->each(function ($proxy) use ($package, $proxies) {
                $proxies->push(array_merge($proxy, array_only($package, ['login', 'password'])));
            });
        });

        $data = [
            'proxies' => $proxies->toArray(),
        ];

        $this->filesystem->put('proxy_bonanza.json', json_encode($data));

        return;
    }

    protected function client()
    {
        return new Client([
            'base_uri' => 'https://api.proxybonanza.com/v1/',
            'headers'  => [
                'Authorization' => env('PROXY_BONANZA_KEY'),
            ],
        ]);
    }

    protected function getPackages(): Collection
    {
        $client = $this->client();

        $json = $client->get('userpackages.json')->getBody()->getContents();

        $response = json_decode($json, true);

        if ($response['success'] === false) {
            throw new \Exception('Error when getting proxy packages list');
        }

        return new Collection($response['data']);
    }

    protected function getProxies($packageID)
    {
        $json = $this->client()->get("userpackages/{$packageID}.json")->getBody()->getContents();

        $response = json_decode($json, true);

        if ($response['success'] === false) {
            throw new \Exception("Error when getting proxy list on a package ({$packageID})");
        }

        return new Collection($response['data']['ippacks']);
    }
}
