<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class ProxyManager
{
    public static function takeOne()
    {
        $chosen = static::all()->random();

        return self::proxyString($chosen);
    }

    public static function all(): Collection
    {
        /** @var Filesystem $fs */
        $fs = app(Filesystem::class);

        if ( ! $fs->exists('proxies.json')) {
            return null;
        }

        $json = $fs->get('proxies.json');
        $data = json_decode($json, true);

        return collect($data['proxies'])->filter(function ($proxy) {
            $blacklist = config('network.outgoing.blacklist');

            return ! in_array($proxy['ip'], $blacklist);
        });
    }

    public static function proxyString($chosen): string
    {
        return "http://{$chosen['login']}:{$chosen['password']}@{$chosen['ip']}:{$chosen['port_http']}";
    }
}