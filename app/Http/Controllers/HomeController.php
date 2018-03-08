<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends AuthRequiredController
{
    public function index(Request $request)
    {
        // Dashboard Rendering
        return view('home');
    }
}
