<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;

class ProxyManager
{
    public static function takeOne()
    {
        /** @var Filesystem $fs */
        $fs = app(Filesystem::class);

        if ( ! $fs->exists('proxies.json')) {
            return null;
        }

        $json = $fs->get('proxies.json');
        $data = json_decode($json, true);

        $proxies = collect($data['proxies']);

        $chosen = $proxies->random();

        return "http://{$chosen['login']}:{$chosen['password']}@{$chosen['ip']}:{$chosen['port_http']}";
    }
}