<?php

namespace App\Http\Controllers\API;

use App\Exceptions\AmazonException;
use App\Http\Controllers\AuthRequiredController;
use App\Sourcing\Amazon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends AuthRequiredController
{
    public function inspect(Request $request, $id)
    {
        try {
            return Amazon::inspect($id);
        } catch (AmazonException $exception) {
            if ($exception->getCode() === 'AWS.InvalidParameterValue') {
                return new Response(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
            }
        }
    }
}
