<?php

namespace App\Http\Controllers;

use DTS\eBaySDK\Trading\Enums\ListingStatusCodeType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ItemsController extends AuthRequiredController
{
    public function index(Request $request)
    {
        $user = $this->resolveCurrentUser($request);

        $accounts = $this->buildAccountsQueryBasedOnCurrentRequest($request)->get();

        $itemsQuery = $this->buildItemsQueryBasedOnCurrentRequestAndAccounts($request, $accounts);

        $allItems = (clone $itemsQuery)->get();

        $totalOrders     = $allItems->sum('orders_count');
        $totalEarning    = $allItems->sum('earning');
        $totalItemsValue = $allItems->sum('price');
        $earningPerItem  = $allItems->count() ? $totalEarning / $allItems->count() : 0;
        $hasSaleItems    = $allItems->filter(function ($item) {
            return $item['orders_count'] > 0;
        });
        $saleThroughRate = $allItems->count() ? $hasSaleItems->count() / $allItems->count() : 0;

        $items = $itemsQuery->paginate();

        return view(
            'listings.index',
            compact('user', 'accounts', 'items', 'totalOrders', 'totalEarning', 'totalItemsValue', 'saleThroughRate', 'earningPerItem')
        );
    }

    protected function buildAccountsQueryBasedOnCurrentRequest(Request $request)
    {
        $query = $this->resolveCurrentUser($request)->accounts();

        if ($request['account']) {
            $query->where('username', $request['account']);
        }

        return $query;
    }

    protected function buildItemsQueryBasedOnCurrentRequestAndAccounts(Request $request, Collection $accounts = null)
    {
        $query = $this->resolveCurrentUser($request)
                      ->items()
                      ->withCount('orders')
                      ->oldest('start_time');

        # ACCOUNTS
        if ($accounts instanceof Collection) {
            $query->whereIn('account_id', $accounts->pluck('id'));
        }

        # STATUS
        switch (strtolower($request['status'])) {
            case 'active':
                $query->where('status', ListingStatusCodeType::C_ACTIVE);
                break;
            case 'ended':
                $query->where('status', ListingStatusCodeType::C_COMPLETED);
                break;
            default:
                break;
        }

        # HAS SALE
        switch (strtolower($request['has_sale'])) {
            case 'has':
                $query->has('orders');
                break;
            case 'doesnthas':
                $query->doesntHave('orders');
                break;
            case 'any':
            default:
                break;
        }

        return $query;
    }
}
