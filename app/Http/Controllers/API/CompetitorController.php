<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Spy\Competitor;
use Illuminate\Http\Request;

class CompetitorController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'competitor' => 'required',
        ]);

        // Amazon Page => Seed Keyword => Competitors => Items
        Competitor::spy($request['username']);

        return ['success' => true];
    }
}
