<?php

namespace App\Http\Controllers;

abstract class AuthRequiredController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
}