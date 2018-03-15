<?php

namespace App\Http\Controllers;

use App\Http\Resources\Tracker as TrackerResource;
use App\Item;
use App\Ranking\Tracker;
use Illuminate\Http\Request;

class TrackersController extends Controller
{
    public function itemTrackers(Request $request, Item $item)
    {
        return TrackerResource::collection($item->trackers()->get());
    }

    public function addTrackerForItem(Request $request, Item $item)
    {
        $this->validate($request, [
            'keyword' => 'required',
        ]);

        return $item->track($request['keyword']);
    }

    public function deleteTracker(Request $request, Tracker $tracker)
    {
        $success = $tracker->delete();

        return compact('success');
    }
}
