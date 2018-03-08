<?php

namespace App\Http\Controllers;

use App\Account;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrdersController extends AuthRequiredController
{
    public function index(Request $request)
    {
        $user = $this->resolveCurrentUser($request)->load('accounts');

        $orderQuery = $user->orders();

        # WHEN HAS SELLER FILTER
        if ($request->has('seller')) {
            $orderQuery->whereHas('account', function (Builder $query) use ($request) {
                $query->where('username', $request['seller']);
            });
        }

        # PAGINATE ORDERS
        $orders = $orderQuery->with('account', 'transactions')->latest('created_time')->paginate(20);

        # REFRESHING
        if ($request['refresh']) {
            /** @noinspection PhpUndefinedMethodInspection */
            $user['accounts']->each(function (Account $account) {
                $account->syncOrdersByCreatedTimeRange(Carbon::now()->subDay(), Carbon::now());
            });

            return redirect()->refresh();
        }

        # RETURN VIEW
        return view('orders.index', compact('orders', 'user'));
    }
}
