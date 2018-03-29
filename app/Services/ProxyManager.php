<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class ProxyManager
{
    public static function takeOne()
    {
        return static::all()->random();
    }

    public static function all(): Collection
    {
        $proxies = config('network.outgoing.proxies');

        return collect($proxies);
    }

    public static function proxyBonanza(): Collection
    {
        /** @var Filesystem $fs */
        $fs = app(Filesystem::class);

        if ( ! $fs->exists('proxy_bonanza.json')) {
            return null;
        }

        $json = $fs->get('proxy_bonanza.json');
        $data = json_decode($json, true);

        return collect($data['proxies']);
    }

    public static function proxyString($chosen): string
    {
        return "http://{$chosen['login']}:{$chosen['password']}@{$chosen['ip']}:{$chosen['port_http']}";
    }
}