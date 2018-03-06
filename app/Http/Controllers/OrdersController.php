<?php

namespace App\Http\Controllers;

use App\Exceptions\CanNotFetchProductInformation;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $this->resolveCurrentUser($request)->load('accounts');

        $activeSeller = null;

        $orderQuery = $user->orders();

        if ($request->has('seller')) {
            $orderQuery->whereHas('account', function (Builder $query) use ($request) {
                $query->where('username', $request['seller']);
            });

            $activeSeller = $request['seller'];
        }

        $orders = $orderQuery->with('account', 'transactions')->latest('created_time')->paginate(50);

        return view('orders.index', compact('orders', 'user', 'activeSeller'));
    }

    private function resolveCurrentUser(Request $request = null): User
    {
        return $request ? $request->user() : request()->user();
    }
}
