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
        if ($request->has('end_date')) {
            $endDate = new Carbon($request['end_date']);
        } else {
            $endDate = $this->defaultEndDate();
        }

        if ($request->has('start_date')) {
            $startDate = new Carbon($request['start_date']);
        } else {
            $startDate = $this->defaultStartDate();
        }

        $previousPeriodEndDate   = $this->previousPeriodEndDate($startDate, $endDate);
        $previousPeriodStartDate = $this->previousPeriodStartDate($startDate, $endDate);

        # USER
        $user = $this->resolveCurrentUser()->load([
            'accounts' => function (HasMany $query) use ($request) {
                if ($request->has('accounts')) {
                    $query->whereIn('username', $request['accounts']);
                }
            },
        ]);

        # QUERY ORDERS & MAKE REPORTER
        $orders         = $this->buildOrdersQuery($request, $startDate, $endDate)->get();
        $ordersLastWeek = $this->buildOrdersQuery($request, $previousPeriodStartDate, $previousPeriodEndDate)->get();

        $orderReports               = new OrderReports($orders);
        $orderReportsPreviousPeriod = new OrderReports($ordersLastWeek);

        # METRICS
        $revenue       = $orderReports->revenue();
        $revenueChange = $revenue ? ($revenue - $orderReportsPreviousPeriod->revenue()) / $revenue : null;

        $fees       = $orderReports->fees();
        $feesChange = $fees ? ($fees - $orderReportsPreviousPeriod->fees()) / $fees : null;

        $cashback       = $orderReports->cashback();
        $cashbackChange = $cashback ? ($cashback - $orderReportsPreviousPeriod->cashback()) / $cashback : null;

        $profit       = $orderReports->profit();
        $profitChange = $profit ? ($profit / $orderReportsPreviousPeriod->profit()) / $profit : null;

        $margin       = $orderReports->margin();
        $marginChange = $margin ? ($margin / $orderReportsPreviousPeriod->margin()) / $margin : null;

        # SALE CHART
        $saleChart = $this->generateSaleChart($orders, $startDate, $endDate);

        // RENDER DASHBOARD
        return view('dashboard', compact(
            'user', 'orders',
            'revenue', 'revenueChange',
            'fees', 'feesChange',
            'cashback', 'cashbackChange',
            'profit', 'profitChange',
            'margin', 'marginChange',
            'startDate', 'endDate', 'previousPeriodStartDate', 'previousPeriodEndDate',
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

        $revenueData = $dates->map(function (Carbon $date) use ($orders) {
            $filtered = $orders->filter(function (Order $order) use ($date) {
                return $date->isSameDay($order['created_time']);
            });

            return $filtered->sum('total');
        })->toArray();

        $ordersData = $dates->map(function (Carbon $date) use ($orders) {
            $filtered = $orders->filter(function (Order $order) use ($date) {
                return $date->isSameDay($order['created_time']);
            });

            return $filtered->count();
        })->toArray();

        $maxOrdersAxis = (round(max($ordersData) / 5) + 1) * 5;

        return [
            'type' => 'bar',

            'data' => [
                'labels'   => $dates->toDateStringCollection()->toArray(),
                'datasets' => [
                    [
                        'type'            => 'bar',
                        'label'           => 'Orders',
                        'backgroundColor' => 'rgba(6, 84, 186, 0.6)',
                        'borderColor'     => 'rgba(6, 84, 186, 1)',
                        'data'            => $ordersData,
                        'yAxisID'         => 'ordersCount',
                    ],
                    [
                        'type'    => 'line',
                        'label'   => 'Revenue',
//                        'backgroundColor' => 'rgba(6, 84, 186, 0.6)',
//                        'borderColor'     => 'rgba(6, 84, 186, 1)',
                        'data'    => $revenueData,
                        'yAxisID' => 'revenue',
                    ],
                ],
            ],

            'options' => [
                'scales' => [
                    'xAxes' => [
                        [
                            'gridLines' => [
                                'display' => false,
                            ],
                        ],
                    ],
                    'yAxes' => [
                        [
                            'id'        => 'ordersCount',
                            'position'  => 'left',
                            'ticks'     => [
                                'beginAtZero' => true,
                                'max'         => $maxOrdersAxis,
                                'stepSize'    => 3,
                            ],
                            'gridLines' => [
                                'display' => false,
                            ],
                        ],
                        [
                            'id'        => 'revenue',
                            'position'  => 'right',
                            'ticks'     => [
                                'beginAtZero' => true,
                                'max'         => 500,
                                'stepSize'    => 50,
                            ],
                            'gridLines' => [
                                'display' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function defaultEndDate(): \Carbon\Carbon
    {
        return Carbon::now()->endOfWeek();
    }

    protected function defaultStartDate(): \Carbon\Carbon
    {
        return Carbon::now()->startOfWeek();
    }

    protected function previousPeriodEndDate(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Carbon\Carbon
    {
        return (clone $startDate)->subDay();
    }

    protected function previousPeriodStartDate(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Carbon\Carbon
    {
        return (clone $this->previousPeriodEndDate($startDate, $endDate))->subDays($endDate->diffInDays($startDate));
    }
}
