@extends('layouts.app')

@php
    /** @var \Illuminate\Database\Eloquent\Collection $orders */
    $dateRangeText = "Date Range: {$startDate->toDateString()} – {$endDate->toDateString()} (vs. {$previousPeriodStartDate->toDateString()} – {$previousPeriodEndDate->toDateString()})"
@endphp

@section('content')
    <div class="container-fluid">

        <!-- FILTERS -->
        <div class="row">
            <div class="col-xl-12 d-flex justify-content-between">
                <span>All Accounts ({{ $user->accounts->count() }})</span>
                <span>{{ $dateRangeText }}</span>
            </div>
        </div>

        <!-- PERFORMANCE METRICS -->
        <div class="row">
            <div class="col-xl-6">
                <div class="row">
                    <div class="col-xl-12">
                        @include('dashboard.overview')
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">

                        <div class="card">
                            <div class="card-header">Orders ({{ $orders->count() }})</div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th>Record <i class="fa fa-caret-down"></i></th>
                                        <th>Total</th>
                                        <th>FVF</th>
                                        <th>PPF</th>
                                        <th>COG</th>
                                        <th>Profit</th>
                                        <th>Margin</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($orders->sortByDesc('created_time') as $order)
                                        @php
                                            $textClass = $order['profit'] > 0.5 ? 'text-success' : ($order['profit'] > 0 ? 'text-warning' :'text-danger');
                                        @endphp
                                        <tr>
                                            <td>{{ $order['account']['username'] }}</td>
                                            <td>{{ $order['record'] }}</td>
                                            <td>{{ usd($order['total']) }}</td>
                                            <td>{{ usd($order['final_value_fee']) }}</td>
                                            <td>{{ usd($order['paypal_fee']) }}</td>
                                            <td>{{ usd($order['cog']) }}</td>
                                            <td class="{{ $textClass }}">{{ usd($order['profit']) }}</td>
                                            <td>{{ percent($order['margin']) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="row">
                    <div class="col-xl-12">
                        <sale-chart :config="{{ json_encode($saleChart) }}" inline-template>
                            <div class="card">
                                <div class="card-header">Sale Chart</div>

                                <div class="card-body">
                                    <canvas id="sale-chart" height="100"></canvas>
                                </div>
                            </div>
                        </sale-chart>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
