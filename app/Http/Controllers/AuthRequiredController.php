<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

abstract class AuthRequiredController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function resolveCurrentUser(Request $request = null): User
    {
        return $request instanceof Request ? $request->user() : request()->user();
    }
}