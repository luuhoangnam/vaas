<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AuthRequiredController;
use App\Support\Amazon;
use Illuminate\Http\Request;

class ProductController extends AuthRequiredController
{
    public function inspect(Request $request, $id)
    {
        return Amazon::inspect($id);
    }
}
