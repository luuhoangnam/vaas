<?php

namespace App\Http\Controllers;

use App\Order;
use App\Reporting\OrderReports;
use DTS\eBaySDK\Trading\Enums\OrderStatusCodeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HomeController extends AuthRequiredController
{
    public function index(Request $request)
    {
        if ( ! $this->resolveCurrentUser($request)->isDeveloper()) {
            return view('home');
        }

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
        $ordersQuery = $this->buildOrdersQuery($request, $startDate, $endDate);

        $orders          = $ordersQuery->get();
        $ordersPaginated = $ordersQuery->paginate();
        $ordersLastWeek  = $this->buildOrdersQuery($request, $previousPeriodStartDate, $previousPeriodEndDate)->get();

        $orderReports               = new OrderReports($orders);
        $orderReportsPreviousPeriod = new OrderReports($ordersLastWeek);

        # METRICS
        $ordersCount       = $orderReports->count();
        $ordersCountChange = $ordersCount ? ($ordersCount - $orderReportsPreviousPeriod->count()) / $ordersCount : null;

        $revenue       = $orderReports->revenue();
        $revenueChange = $revenue ? ($revenue - $orderReportsPreviousPeriod->revenue()) / $revenue : null;

        $fees       = $orderReports->fees();
        $feesChange = $fees ? ($fees - $orderReportsPreviousPeriod->fees()) / $fees : null;

        $cashback       = $orderReports->cashback();
        $cashbackChange = $cashback ? ($cashback - $orderReportsPreviousPeriod->cashback()) / $cashback : null;

        $profit       = $orderReports->profit();
        $profitChange = $profit ? ($profit - $orderReportsPreviousPeriod->profit()) / $profit : null;

        $margin       = $orderReports->margin();
        $marginChange = $margin ? ($margin - $orderReportsPreviousPeriod->margin()) / $margin : null;

        $aov       = $orderReports->averageOrderValue();
        $aovChange = $aov ? ($aov - $orderReportsPreviousPeriod->averageOrderValue()) / $aov : null;

        $cog       = $orderReports->costOfGoods();
        $cogChange = $cog ? ($cog - $orderReportsPreviousPeriod->costOfGoods()) / $cog : null;

        # SALE CHART
        $saleChart = $this->generateSaleChart($orders, $startDate, $endDate);

        # EBAY CATEGORY ANALYTICS
        $categoryChart = $this->generateCategoryChart($orders);

        # NEW LISTINGS
        $newItemsQuery = $user->items()
                              ->with('account')
                              ->whereHas('account', function (Builder $query) use ($request) {
                                  if ($request->has('accounts')) {
                                      $query->whereIn('username', $request['accounts']);
                                  }
                              })
                              ->whereDate('start_time', '>=', $startDate)
                              ->whereDate('start_time', '<=', $endDate)
                              ->orderByDesc('start_time');

        $newItems          = $newItemsQuery->get();
        $newItemsPaginated = $newItemsQuery->paginate();

        $pageTitle = 'Dashboard';

        // RENDER DASHBOARD
        return view('dashboard', compact(
            'pageTitle',
            'user', 'orders', 'ordersPaginated', 'newItems', 'newItemsPaginated',
            'revenue', 'revenueChange',
            'ordersCount', 'ordersCountChange',
            'fees', 'feesChange',
            'cashback', 'cashbackChange',
            'aov', 'aovChange',
            'cog', 'cogChange',
            'profit', 'profitChange',
            'margin', 'marginChange',
            'startDate', 'endDate', 'previousPeriodStartDate', 'previousPeriodEndDate',
            'saleChart', 'categoryChart'
        ));
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

        $query->latest('created_time');

        return $query;
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

    protected function defaultEndDate(): \Carbon\Carbon
    {
        return Carbon::today();
    }

    protected function defaultStartDate(): \Carbon\Carbon
    {
        return Carbon::today()->subDays(30);
    }

    protected function previousPeriodEndDate(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Carbon\Carbon
    {
        return (clone $startDate)->subDay();
    }

    protected function previousPeriodStartDate(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Carbon\Carbon
    {
        return (clone $this->previousPeriodEndDate($startDate, $endDate))->subDays($endDate->diffInDays($startDate));
    }

    protected function generateCategoryChart(\Illuminate\Database\Eloquent\Collection $orders)
    {
        $orders->load('transactions.item');

        $categoryIds = $orders->pluck('transactions.item')->groupBy('primary_category_id');

//        dd($orders->random()->transactions->random()->item);

        $data = [
            'count'   => [65, 59, 90, 81, 56, 55, 40],
            'revenue' => [28, 48, 40, 19, 96, 27, 100],
        ];

        return [
            'type' => 'radar',

            'data' => [
                'labels'   => ['Eating', 'Drinking', 'Sleeping', 'Designing', 'Coding', 'Cycling', 'Running'],
                'datasets' => [
                    [
                        'label'                     => 'Orders',
                        'fill'                      => true,
                        'backgroundColor'           => 'rgba(255, 99, 132, 0.2)',
                        'borderColor'               => 'rgb(255, 99, 132)',
                        'pointBackgroundColor'      => 'rgb(255, 99, 132)',
                        'pointBorderColor'          => '#fff',
                        'pointHoverBackgroundColor' => '#fff',
                        'pointHoverBorderColor'     => 'rgb(255, 99, 132)',
                        'data'                      => [65, 59, 90, 81, 56, 55, 40],
                    ],
                    [
                        'label'                     => 'Profit',
                        'fill'                      => true,
                        'backgroundColor'           => 'rgba(54, 162, 235, 0.2)',
                        'borderColor'               => 'rgb(54, 162, 235)',
                        'pointBackgroundColor'      => 'rgb(54, 162, 235)',
                        'pointBorderColor'          => '#fff',
                        'pointHoverBackgroundColor' => '#fff',
                        'pointHoverBorderColor'     => 'rgb(54, 162, 235)',
                        'data'                      => [28, 48, 40, 19, 96, 27, 100],
                    ],
                ],
            ],

            'options' => [
                //
            ],
        ];
    }
}
