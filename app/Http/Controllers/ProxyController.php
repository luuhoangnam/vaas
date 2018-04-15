<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProxyController extends Controller
{
    public function pac(Request $request)
    {
        $template = <<<PACSCRIPT
    function FindProxyForURL(url, host) {
        return "PROXY %1\$s";
    }
PACSCRIPT;

        $proxies = collect(config('network.outgoing.proxies'));
        $script = sprintf($template, $proxies->random());
dd($script);
        return response($script, 200, [
            'Content-Type' => 'application/x-ns-proxy-autoconfig',
        ]);
    }
}
