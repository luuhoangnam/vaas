<?php

namespace App\Http\Controllers;

use App\Order;
use App\Reporting\OrderReports;
use DTS\eBaySDK\Trading\Enums\OrderStatusCodeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportsController extends AuthRequiredController
{
    public function byDays(Request $request)
    {
        return $this->generateReport($request, 'daily');
    }

    public function byWeeks(Request $request)
    {
        return $this->generateReport($request, 'weekly');
    }

    public function byMonths(Request $request)
    {

    }

    public function byYears(Request $request)
    {

    }

    protected function buildOrdersQuery(Request $request, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
    {
        $user = $this->resolveCurrentUser($request);

        $query = $user->orders()->with('account', 'transactions');

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

    protected function defaultEndDate(): \Carbon\Carbon
    {
        return Carbon::today();
    }

    protected function defaultStartDate(): \Carbon\Carbon
    {
        return Carbon::today()->subDays(30);
    }

    protected function generateSaleChart(Collection $orders, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
    {
        $dates = date_range($startDate, $endDate);

        $data = $dates->map(function (Carbon $date) use ($orders) {
            $filtered = $orders->filter(function (Order $order) use ($date) {
                return $order['status'] == OrderStatusCodeType::C_COMPLETED && $date->isSameDay($order['created_time']);
            });

            return [
                'revenue' => round($filtered->sum('total'), 2),
                'profit'  => round($filtered->sum('profit'), 2),
                'count'   => $filtered->count(),
            ];
        });

        $counts   = $data->pluck('count');
        $revenues = $data->pluck('revenue');
        $profits  = $data->pluck('profit');

        $orderAxisStep = 5;
        $maxOrdersAxis = (round($counts->max() / $orderAxisStep) + 1) * $orderAxisStep;

        $revenueAxisStep = 50;
        $maxRevenueAxis  = (round($revenues->max() / $revenueAxisStep) + 1) * $revenueAxisStep;

        $profitAxisStep = 10;
        $maxProfitAxis  = (round($profits->max() / $profitAxisStep) + 1) * $profitAxisStep;

        $countData   = $counts->toArray();
        $revenueData = $revenues->toArray();
        $profitData  = $profits->toArray();

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
                        'data'            => $countData,
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
                    [
                        'type'            => 'line',
                        'label'           => 'Profit',
                        'fill'            => false,
                        'backgroundColor' => 'rgba(121, 250, 76, .6)',
                        'borderColor'     => 'rgb(121, 250, 76)',
                        'data'            => $profitData,
                        'yAxisID'         => 'profit',
                    ],
                ],
            ],

            'options' => [
                'tooltips' => [
                    'titleFontFamily' => 'Fira Code',
                ],
                'scales'   => [
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
                                'stepSize'    => $orderAxisStep,
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
                                'max'         => $maxRevenueAxis,
                                'stepSize'    => $revenueAxisStep,
                            ],
                            'gridLines' => [
                                'display' => false,
                            ],
                        ],
                        [
                            'id'        => 'profit',
                            'position'  => 'right',
                            'ticks'     => [
                                'beginAtZero' => true,
                                'max'         => $maxProfitAxis,
                                'stepSize'    => $profitAxisStep,
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

    protected function generateReport(Request $request, $type = 'daily')
    {
        $this->validate($request, [
            'accounts' => 'array',
        ]);

        # USER
        $user = $this->resolveCurrentUser()->load([
            'accounts' => function (HasMany $query) use ($request) {
                if ($request->has('accounts')) {
                    $query->whereIn('username', $request['accounts']);
                }
            },
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

        $orders = $this->buildOrdersQuery($request, $startDate, $endDate)->get();

        $reporter = new OrderReports($orders);

        $records = $this->makeRecords($reporter, $type, $startDate, $endDate);

        # SALE CHART
        $saleChart = $this->generateSaleChart($orders, $startDate, $endDate);


        $viewData = compact('user', 'records', 'startDate', 'reporter', 'endDate', 'saleChart');

        if ($type === 'daily') {
            return view('reports.sale_by_days', $viewData);
        }

        if ($type === 'weekly') {
            return view('reports.sale_by_weeks', $viewData);
        }

        throw new \Exception('Unknown report type');
    }

    protected function makeRecords(OrderReports $reporter, $type, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
    {
        if ($type === 'weekly') {
            return date_range($startDate, $endDate)
                ->map(function (\Carbon\Carbon $startDayOfWeek) use ($reporter, $type) {
                    return [
                        'week'     => $startDayOfWeek->weekOfYear,
                        'reporter' => $reporter->onWeek($startDayOfWeek),
                        'previous' => $reporter->onWeek($startDayOfWeek->subWeek()),
                    ];
                });
        }

        return date_range($startDate, $endDate)->map(function (\Carbon\Carbon $date) use ($reporter, $type) {
            return [
                'date'     => $date,
                'reporter' => $reporter->onDate($date),
                'previous' => $reporter->onDate($date->subDay()),
            ];
        });
    }
}
