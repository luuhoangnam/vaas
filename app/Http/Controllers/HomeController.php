<?php

namespace App\Http\Controllers;

use App\Order;
use App\Reporting\OrderReports;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HomeController extends AuthRequiredController
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'accounts' => 'array',
        ]);

        # DATE RANGE
        $startDate = Carbon::now()->startOfWeek();
        $endDate   = Carbon::now()->endOfWeek();

        # USER
        $user = $this->resolveCurrentUser()->load([
            'accounts' => function (HasMany $query) use ($request) {
                if ($request->has('accounts')) {
                    $query->whereIn('username', $request['accounts']);
                }
            },
        ]);

        # QUERY ORDERS & MAKE REPORTER
        $orders = $this->buildOrdersQuery($request, $startDate, $endDate)->get();

        $ordersLastWeek = $this->buildOrdersQuery(
            $request,
            (clone $startDate)->subWeek(), (clone $endDate)->subWeek()
        )->get();

        $orderReports         = new OrderReports($orders);
        $orderReportsLastWeek = new OrderReports($ordersLastWeek);

        # METRICS
        $revenue       = $orderReports->revenue();
        $revenueChange = $revenue ? ($revenue - $orderReportsLastWeek->revenue()) / $revenue : null;

        $fees       = $orderReports->fees();
        $feesChange = $fees ? ($fees - $orderReportsLastWeek->fees()) / $fees : null;

        $cashback       = $orderReports->cashback();
        $cashbackChange = $cashback ? ($cashback - $orderReportsLastWeek->cashback()) / $cashback : null;

        $profit       = $orderReports->profit();
        $profitChange = $profit ? ($profit / $orderReportsLastWeek->profit()) / $profit : null;

        $margin       = $orderReports->margin();
        $marginChange = $margin ? ($margin / $orderReportsLastWeek->margin()) / $margin : null;

        # SALE CHART
        $saleChart = $this->generateSaleChart($orders, $startDate, $endDate);

        // RENDER DASHBOARD
        return view('dashboard', compact(
            'user',
            'revenue', 'revenueChange',
            'fees', 'feesChange',
            'cashback', 'cashbackChange',
            'profit', 'profitChange',
            'margin', 'marginChange',
            'startDate', 'endDate',
            'saleChart'
        ));
    }

    protected function buildOrdersQuery(Request $request, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
    {
        $user = $this->resolveCurrentUser($request);

        $query = $user->orders()->with('transactions');

        # DATE RANGE
        $query->whereDate('created_time', '>=', $startDate)
              ->whereDate('created_time', '<=', $endDate);

        # ACCOUNTS
        if ($request->has('accounts')) {
            $query->whereHas('account', function (Builder $builder) use ($request) {
                $builder->whereIn('username', $request['accounts']);
            });
        }

        return $query;
    }

    protected function generateSaleChart(Collection $orders, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
    {
        $dates = date_range($startDate, $endDate);

        $data = $dates->map(function (Carbon $date) use ($orders) {
            $filtered = $orders->filter(function (Order $order) use ($date) {
                return $date->isSameDay($order['created_time']);
            });

            return $filtered->count();
        })->toArray();

        return [
            'type' => 'bar',

            'data' => [
                'labels'   => $dates->toDateStringCollection()->toArray(),
                'datasets' => [
                    [
                        'label'           => 'Orders',
                        'backgroundColor' => 'rgba(6, 84, 186, 0.6)',
                        'borderColor'     => 'rgba(6, 84, 186, 1)',
                        'data'            => $data,
                    ],
                ],
            ],

            'options' => [],
        ];
    }
}
