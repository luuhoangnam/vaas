<?php

namespace App\Console\Commands;

use App\Services\ProxyManager;
use Campo\UserAgent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Console\Command;

class CheckProxy extends Command
{
    protected $signature = 'proxy:check';

    protected $description = 'Check Working Proxy';

    public function handle()
    {
        $proxies = ProxyManager::all();

        $proxies->each(function ($proxy) {

            $config = [
                'timeout' => 60,
                'proxy'   => $proxy,
                'headers' => [
                    'User-Agent' => UserAgent::random(),
                ],
            ];

            $client = new Client($config);

            try {
                $response = $client->get('https://www.amazon.com');

                if ($response->getStatusCode() === 200) {
                    $this->info("Proxy: {$proxy} => OK!");
                } else {
                    $this->warn("Proxy: {$proxy} => {$response->getStatusCode()}");
                }
            } catch (ServerException $exception) {
                $this->error("Proxy: {$proxy} => {$exception->getResponse()->getStatusCode()}");
            }
        });

        $this->info('Done');
    }
}
