@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 col-xl-1">
                <div class="card">
                    <div class="card-header">Accounts ({{ $user['accounts']->count() }})</div>

                    <div class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
                        <a class="nav-link {{ is_null($activeSeller) ? 'active' : '' }}"
                           href="{{ route('orders') }}">
                            All
                        </a>

                        @foreach($user['accounts'] as $account)
                            <a class="nav-link {{ $activeSeller === $account['username'] ? 'active' : '' }}"
                               href="{{ route('orders') }}?seller={{ $account['username'] }}" target="_self">
                                {{ $account['username'] }}
                            </a>
                        @endforeach
                    </div>

                </div>
            </div>

            <div class="col-xl-11 col-lg-10 col-md-9">
                <div class="card">
                    <div class="card-header">Orders ({{ $orders->total() }})</div>

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="table-responsive-xl">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th scope="col">Record <i class="fa fa-caret-down"></i></th>
                                <th scope="col">Item</th>
                                <th scope="col">Buyer</th>
                                <th scope="col">Total</th>
                                <th scope="col">FVF</th>
                                <th scope="col">PPF</th>
                                <th scope="col">COG</th>
                                <th scope="col">Profit</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($orders as $order)
                                @php
                                    $transaction = $order['transactions']->first();
                                    $item = $transaction['item'];
                                @endphp

                                <tr>
                                    <td scope="row" class="">
                                        <a href="{{ $order['ebay_link'] }}">{{ $order['record'] }}</a>
                                    </td>
                                    <td>
                                        (<span class="text-decoration">{{ $transaction['item_id'] }}</span>)
                                        <a href="{{ $item['ebay_link'] }}">{{ $transaction['item_title'] }}</a>
                                    </td>
                                    <td>
                                        <a href="{{ $order['buyer_ebay_link'] }}">{{ $order['buyer_username'] }}</a>
                                    </td>
                                    <td>{{ usd($order['total']) }}</td>
                                    <td>{{ usd($order['final_value_fee']) }}</td>
                                    <td>{{ usd($order['paypal_fee']) }}</td>
                                    <td>{{ $order['cog'] ? usd($order['cog']) : 'N/A' }}</td>
                                    <td>
                                    <span class="{{ $order['profit'] > 0 ? 'text-success' : 'text-warning' }}">
                                        {{ $order['profit'] ? usd($order['profit']) : 'N/A' }}
                                    </span>
                                    </td>
                                    <td class="text-right">
                                        @if ($item['cashback_link'])
                                            <a class="btn btn-primary btn-sm" target="_blank"
                                               href="{{ $item['cashback_link'] }}">
                                                Place Order
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-disabled">Can Not Fetch</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
