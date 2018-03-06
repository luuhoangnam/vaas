<?php

namespace App\Http\Controllers;

use App\Account;
use App\Exceptions\CanNotFetchProductInformation;
use App\User;
use function GuzzleHttp\Psr7\uri_for;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $this->resolveCurrentUser($request)->load('accounts');

        $orderQuery = $user->orders();

        if ($request->has('seller')) {
            $orderQuery->whereHas('account', function (Builder $query) use ($request) {
                $query->where('username', $request['seller']);
            });
        }

        $orders = $orderQuery->with('account', 'transactions')->latest('created_time')->paginate(50);

        if ($request['refresh']) {
            /** @noinspection PhpUndefinedMethodInspection */
            $user['accounts']->each(function (Account $account) {
                $account->syncOrdersByCreatedTimeRange(Carbon::now()->subDay(), Carbon::now());
            });

            return redirect()->refresh();
        }

        return view('orders.index', compact('orders', 'user'));
    }

    private function resolveCurrentUser(Request $request = null): User
    {
        return $request ? $request->user() : request()->user();
    }
}
