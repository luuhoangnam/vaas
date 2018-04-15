<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProxyController extends Controller
{
    public function pac(Request $request)
    {
        $proxies = collect(config('network.outgoing.proxies'));
        
        $script = view('proxy', compact('proxies'));

        return response($script, 200, [
            'Content-Type' => 'application/x-ns-proxy-autoconfig',
        ]);
    }
}
