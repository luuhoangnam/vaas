<?php

namespace App\Console\Commands;

use App\Services\ProxyManager;
use Campo\UserAgent;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class CheckProxy extends Command
{
    protected $signature = 'proxy:check';

    protected $description = 'Check Working Proxy';

    public function handle()
    {
        $proxies = ProxyManager::all();

        $proxies->each(function ($proxy) {

            $proxyString = "http://{$proxy['login']}:{$proxy['password']}@{$proxy['ip']}:{$proxy['port_http']}";

            $config = [
                'timeout' => 60,
                'proxy'   => $proxyString,
                'headers' => [
                    'User-Agent' => UserAgent::random(),
                ],
            ];

            $client = new Client($config);

            $response = $client->get('https://www.amazon.com');

            if ($response->getStatusCode() === 200) {
                $this->info("Proxy: {$proxyString} => OK!");
            } else {
                $this->error("Proxy: {$proxyString} => {$response->getStatusCode()}");
            }
        });

        $this->info('Done');
    }
}
