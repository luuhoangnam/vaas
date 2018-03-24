<?php

namespace App\Http\Controllers\Spying;

use App\Http\Controllers\AuthRequiredController;
use App\Jobs\FindItemAdvanced;
use App\Listeners\FindActiveSellingItems;
use App\Spy\Competitor;
use Illuminate\Http\Request;

class CompetitorController extends AuthRequiredController
{
    public function index(Request $request)
    {
        $competitors = Competitor::query()->withCount('items')->paginate(25);

        return view('competitors.index', compact('competitors'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
        ]);

        Competitor::query()->updateOrCreate(
            ['username' => $request['username']],
            ['username' => $request['username']]
        );

        // Auto Research
        FindItemAdvanced::dispatch($request['username']);

        return redirect()->route('competitor');
    }

    public function delete(Request $request, $username)
    {
        Competitor::find($username)->delete();

        return redirect()->route('competitor');
    }
}
