<?php

namespace App\eBay;

use DTS\eBaySDK\Constants\SiteIds;
use DTS\eBaySDK\Sdk;

class AppPool
{
    public static function balancing($service = 'trading')
    {
        $apps = config('ebay.apps');

        $pool = [];

        foreach ($apps as $key => $app) {
            $usage = cache("apps.{$app['app_id']}.{$service}.usage");
            $quota = cache("apps.{$app['app_id']}.{$service}.quota");

            if ($quota) {

                $rate = $usage / $quota;

                $weight = round((1 - $rate) * 100);

                $pool = array_merge($pool, array_fill(0, $weight, $app));
            } else {
                $pool[] = $app;
            }
        }

        return array_random($pool);
    }

    public static function random(): Sdk
    {
        return static::sdk(static::balancing());
    }

    public static function sdk(array $credentials): Sdk
    {
        return new Sdk([
            'siteId'      => SiteIds::US,
            'credentials' => [
                'appId'  => $credentials['app_id'],
                'certId' => $credentials['cert_id'],
                'devId'  => $credentials['dev_id'],
            ],
            'Finding'     => [
                'apiVersion' => '1.13.0', // Release: 2014-10-21
            ],
            'Shopping'    => [
                'apiVersion' => '1027', // Release: 2017-Aug-04
            ],
            'Trading'     => [
                'apiVersion' => '1047', // Release: 2018-Feb-02
            ],
        ]);
    }
}