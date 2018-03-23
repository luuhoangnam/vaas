<?php

namespace App\Http\Controllers;

use App\Item;
use App\Order;
use App\Ranking\Record;
use App\Ranking\Tracker;
use App\Reporting\ItemReports;
use DTS\eBaySDK\Trading\Enums\ListingStatusCodeType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ItemsController extends AuthRequiredController
{
    public function index(Request $request)
    {
        $user = $this->resolveCurrentUser($request);

        $accounts = $this->buildAccountsQueryBasedOnCurrentRequest($request)->get();

        $itemsQuery = $this->buildItemsQueryBasedOnCurrentRequestAndAccounts($request, $accounts);

        $allItems = $itemsQuery->get();

        $reporter = new ItemReports($allItems);

        $items = $itemsQuery->paginate();

        return view('items.index', compact('user', 'accounts', 'allItems', 'items', 'reporter'));
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

        # START BETWEEN
        if ($request['start_before']) {
            $query->whereDate('start_time', '<=', new Carbon($request['start_before']));
        }

        if ($request['start_after']) {
            $query->whereDate('start_time', '>=', new Carbon($request['start_after']));
        }

        return $query;
    }

    public function show(Request $request, Item $item)
    {
        $this->authorize('view', $item);

        $item->loadMissing('orders');

        # Sale Performance

        # Sale Chart
        $chart = $this->buildChart($item, $item['start_time'], Carbon::now());

        # Ranking History

        # Revisions

        return view('items.show', compact('item', 'chart'));
    }

    private function buildChart(Item $item, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $dates = date_range($startDate, $endDate);

        $data = $dates->map(function (Carbon $date) use ($item) {
            /** @var Collection $filtered */
            $filtered = $item['orders']->filter(function (Order $order) use ($date) {
                return $date->isSameDay($order['created_time']);
            });

            return [
                'orders'  => $filtered->count(),
                'revenue' => $filtered->sum('total'),
                'rank'    => $this->itemRankForParticularDate($item, $date),
            ];
        });

        $ordersData  = $data->pluck('orders')->toArray();
        $revenueData = $data->pluck('revenue')->toArray();

        $maxOrdersAxis  = (round(max($ordersData) / 5) + 1) * 5;
        $maxRevenueAxis = (round(max($revenueData) / 50) + 1) * 50;

        $config = [
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
                        'yAxisID'         => 'orders',
                    ],
                    [
                        'type'    => 'line',
                        'label'   => 'Revenue',
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
                            'time'      => [
                                'unit' => 'day',
                            ],
                        ],
                    ],
                    'yAxes' => [
                        [
                            'id'        => 'orders',
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
                                'max'         => $maxRevenueAxis,
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

        if ($item['trackers']->count()) {
            $config['data']['datasets'][] = [
                'type'            => 'line',
                'label'           => 'Rank',
                'data'            => $data->pluck('rank')->toArray(),
                'backgroundColor' => 'rgba(255, 255, 255, 0)',
                'borderColor'     => 'rgb(255, 99, 132)',
            ];
        }

        return $config;
    }

    protected function itemRankForParticularDate(Item $item, \Carbon\Carbon $date)
    {
        $tracker = $item['trackers']->first();

        if ($tracker instanceof Tracker) {
            $record = $tracker->recordOnDate(Carbon::today())->first();

            if ($record instanceof Record) {
                return $record['rank'];
            }
        }

        return null;
    }
}
