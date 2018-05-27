<?php

namespace App\Http\Controllers;

use App\Item;
use App\Order;
use App\Reporting\OrderReports;
use Carbon\Carbon as PureCarbon;
use DTS\eBaySDK\Trading\Enums\OrderStatusCodeType;
use Illuminate\Database\Eloquent\Builder;
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

        $previousPeriodEndDate = $this->previousPeriodEndDate($startDate, $endDate);
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

        $orders = $ordersQuery->get();
        $ordersPaginated = $ordersQuery->paginate(50);
        $ordersLastWeek = $this->buildOrdersQuery($request, $previousPeriodStartDate, $previousPeriodEndDate)->get();

        $orderReports = new OrderReports($orders);
        $orderReportsPreviousPeriod = new OrderReports($ordersLastWeek);

        # METRICS
        $ordersCount = $orderReports->count();
        $ordersCountPrev = $orderReportsPreviousPeriod->count();
        $ordersCountChange = $ordersCountPrev ? ($ordersCount - $ordersCountPrev) / $ordersCountPrev : null;

        $revenue = $orderReports->revenue();
        $revenuePrev = $orderReportsPreviousPeriod->revenue();
        $revenueChange = $revenuePrev ? ($revenue - $revenuePrev) / $revenuePrev : null;

        $fees = $orderReports->fees();
        $feesPrev = $orderReportsPreviousPeriod->fees();
        $feesChange = $feesPrev ? ($fees - $feesPrev) / $feesPrev : null;

        $ads = $orderReports->adFee();
        $adsPrev = $orderReportsPreviousPeriod->adFee();
        $adsChange = $adsPrev ? ($ads - $adsPrev) / $adsPrev : null;

        $cashback = $orderReports->cashback();
        $cashbackPrev = $orderReportsPreviousPeriod->cashback();
        $cashbackChange = $cashbackPrev ? ($cashback - $cashbackPrev) / $cashbackPrev : null;

        $profit = $orderReports->profit();
        $profitPrev = $orderReportsPreviousPeriod->profit();
        $profitChange = $profitPrev ? ($profit - $profitPrev) / $profitPrev : null;

        $margin = $orderReports->margin();
        $marginPrev = $orderReportsPreviousPeriod->margin();
        $marginChange = $marginPrev ? ($margin - $marginPrev) / $marginPrev : null;

        $aov = $orderReports->averageOrderValue();
        $aovPrev = $orderReportsPreviousPeriod->averageOrderValue();
        $aovChange = $aovPrev ? ($aov - $aovPrev) / $aovPrev : null;

        $aof = $orderReports->averageOrderProfit();
        $aofPrev = $orderReportsPreviousPeriod->averageOrderProfit();
        $aofChange = $aofPrev ? ($aof - $aofPrev) / $aofPrev : null;

        $cog = $orderReports->costOfGoods();
        $cogPrev = $orderReportsPreviousPeriod->costOfGoods();
        $cogChange = $cogPrev ? ($cog - $cogPrev) / $cogPrev : null;

        $sellThrough = $this->sellThroughInPeriod($startDate, $endDate);
        $sellThroughPrev = $this->sellThroughInPeriod($previousPeriodStartDate, $previousPeriodEndDate);
        $sellThroughChange = $sellThroughPrev ? ($sellThrough - $sellThroughPrev) / $sellThroughPrev : null;

        $cashbackRate = $this->cashbackOrdersCountInPeriod($startDate, $endDate) / $ordersCount;
        $cashbackRatePrev = $this->cashbackOrdersCountInPeriod($previousPeriodStartDate,
                $previousPeriodEndDate) / $ordersCountPrev;
        $cashbackRateChange = $cashbackRatePrev ? ($cashbackRate - $cashbackRatePrev) / $cashbackRatePrev : null;

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

        $newItems = $newItemsQuery->get();
        $newItemsPaginated = $newItemsQuery->paginate(35);

        # ITEM PRICE DISTRIBUTION
        $priceDistributionChart = $this->generatePriceDistributionChart($request, $startDate, $endDate);

        $pageTitle = 'Dashboard';

        // RENDER DASHBOARD
        return view('dashboard', compact(
            'pageTitle',
            'user', 'orders', 'ordersPaginated', 'newItems', 'newItemsPaginated',
            'revenue', 'revenueChange',
            'ordersCount', 'ordersCountChange',
            'fees', 'feesChange',
            'ads', 'adsChange',
            'cashback', 'cashbackChange',
            'aov', 'aovChange', 'aof', 'aofChange',
            'cog', 'cogChange',
            'profit', 'profitChange',
            'margin', 'marginChange',
            'startDate', 'endDate', 'previousPeriodStartDate', 'previousPeriodEndDate',
            'saleChart', 'categoryChart', 'priceDistributionChart', 'sellThrough', 'sellThroughChange',
            'cashbackRate', 'cashbackRateChange'
        ));
    }

    public function cashbackOrdersCountInPeriod(PureCarbon $startDate, PureCarbon $endDate)
    {
        return (clone $this->buildOrdersQuery(request(), $startDate, $endDate))->where('cashback', '>', 0)->count();
    }

    public function sellThroughInPeriod(PureCarbon $startDate, PureCarbon $endDate)
    {
        $baseQuery = $this->resolveCurrentUser()
                          ->items()
                          ->whereHas('account', function (Builder $query) {
                              if (request()->has('accounts')) {
                                  $query->whereIn('username', request('accounts'));
                              }
                          });

        $items = $baseQuery->whereDate('start_time', '>=', $startDate)
                           ->whereDate('start_time', '<=', $endDate);

//        $totalListedItem = (clone $items)->count();

        $totalActiveItem = (clone $baseQuery)->where('status', 'Active')->count();

        $totalSoldListedItem = (clone $items)->whereHas('orders',
            function (Builder $builder) use ($startDate, $endDate) {
                $builder->where('created_time', '>=', $startDate)
                        ->where('created_time', '<=', $endDate);
            })->count();

        return $totalActiveItem ? $totalSoldListedItem / $totalActiveItem : null;
    }

    protected function buildOrdersQuery(Request $request, PureCarbon $startDate, PureCarbon $endDate)
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

    protected function generateSaleChart(Collection $orders, PureCarbon $startDate, PureCarbon $endDate)
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

        $counts = $data->pluck('count');
        $revenues = $data->pluck('revenue');
        $profits = $data->pluck('profit');

        $orderAxisStep = 5;
        $maxOrdersAxis = (round($counts->max() / $orderAxisStep) + 1) * $orderAxisStep;

        $revenueAxisStep = 50;
        $maxRevenueAxis = (round($revenues->max() / $revenueAxisStep) + 1) * $revenueAxisStep;

        $profitAxisStep = 10;
        $maxProfitAxis = (round($profits->max() / $profitAxisStep) + 1) * $profitAxisStep;

        $countData = $counts->toArray();
        $revenueData = $revenues->toArray();
        $profitData = $profits->toArray();

        return [
            'type' => 'bar',

            'data' => [
                'labels'   => $dates->toDateStringCollection()->toArray(),
                'datasets' => [
                    [
                        'type'            => 'bar',
                        'label'           => 'Orders',
                        //                        'backgroundColor' => 'rgba(6, 84, 186, 0.6)',
                        'backgroundColor' => "rgba(54, 162, 235, 0.2)",
                        'borderColor'     => "rgb(54, 162, 235)",
                        'data'            => $countData,
                        'yAxisID'         => 'ordersCount',
                    ],
                    [
                        'type'            => 'line',
                        'label'           => 'Revenue',
                        'fill'            => false,
                        'backgroundColor' => "rgba(255, 205, 86, 0.2)",
                        'borderColor'     => "rgb(255, 205, 86)",
                        'data'            => $revenueData,
                        'yAxisID'         => 'revenue',
                    ],
                    [
                        'type'            => 'line',
                        'label'           => 'Profit',
                        'fill'            => false,
                        'backgroundColor' => "rgba(75, 192, 192, 0.2)",
                        'borderColor'     => "rgb(75, 192, 192)",
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

    protected function defaultEndDate(): PureCarbon
    {
        return Carbon::today();
    }

    protected function defaultStartDate(): PureCarbon
    {
        return Carbon::today()->subDays(30);
    }

    protected function previousPeriodEndDate(PureCarbon $startDate, PureCarbon $endDate): PureCarbon
    {
        return (clone $startDate)->subDay();
    }

    protected function previousPeriodStartDate(PureCarbon $startDate, PureCarbon $endDate): PureCarbon
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

    protected function generatePriceDistributionChart(Request $request, PureCarbon $startDate, PureCarbon $endDate)
    {
        /** @var Builder|Item $baseQuery */
        $baseQuery = Item::active()->since($startDate)->until($endDate)->whereHas(
            'account',
            function (Builder $builder) use ($request) {
                if ($request->has('accounts')) {
                    $builder->whereIn('username', $request['accounts']);
                }
            }
        );

        $lessThanTen = (clone $baseQuery)->where('price', '<=', 10)->count();
        $tenToTwenty = (clone $baseQuery)->where('price', '>', 10)
                                         ->where('price', '<=', 20)
                                         ->count();
        $twentyToForty = (clone $baseQuery)->where('price', '>', 20)
                                           ->where('price', '<=', 40)
                                           ->count();
        $fortyToHundred = (clone $baseQuery)->where('price', '>', 40)
                                            ->where('price', '<=', 100)
                                            ->count();
        $overHundred = (clone $baseQuery)->where('price', '>', 100)->count();

        $labels = ['Price <= $10', '$10 < Price <= $20', '$20 < Price <= $40', '$40 < Price <= $100', 'Price > $100'];
        $data = [$lessThanTen, $tenToTwenty, $twentyToForty, $fortyToHundred, $overHundred];

        return [
            'type' => 'pie',

            'data' => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'           => 'Item Price',
                        "backgroundColor" => [
                            "rgba(255, 99, 132, 0.2)",
                            "rgba(255, 159, 64, 0.2)",
                            "rgba(255, 205, 86, 0.2)",
                            "rgba(75, 192, 192, 0.2)",
                            "rgba(54, 162, 235, 0.2)",
                            "rgba(153, 102, 255, 0.2)",
                            "rgba(201, 203, 207, 0.2)",
                        ],
                        "borderColor"     => [
                            "rgb(255, 99, 132)",
                            "rgb(255, 159, 64)",
                            "rgb(255, 205, 86)",
                            "rgb(75, 192, 192)",
                            "rgb(54, 162, 235)",
                            "rgb(153, 102, 255)",
                            "rgb(201, 203, 207)",
                        ],
                        "borderWidth"     => 1,
                        'data'            => $data,
                    ],
                ],
            ],

            'options' => [
                //
            ],
        ];
    }
}
