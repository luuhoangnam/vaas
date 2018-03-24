<?php

namespace App\Http\Controllers\Spying;

use App\Http\Controllers\AuthRequiredController;
use App\Spy\CompetitorItem;
use Illuminate\Http\Request;

class ItemController extends AuthRequiredController
{
    public function index(Request $request)
    {
        $items = CompetitorItem::query()->paginate(50);

        return view('competitors.items', compact('items'));
    }
}
