<div class="card">
    <div class="card-header">
        Orders ({{ $orders->count() }})
        @foreach($orders->groupBy('account.username') as $username => $accountOrders)
            | {{ $username }} ({{ $accountOrders->count() }})
        @endforeach
    </div>

    <div class="table-responsive">
        <dashboard-orders-table :orders="{{ json_encode($orders) }}" inline-template>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Account</th>
                    <th>Record <i class="fa fa-caret-down"></i></th>
                    <th>Total</th>
                    <th>FVF</th>
                    <th>PPF</th>
                    <th>COG</th>
                    <th>Cashback</th>
                    <th>Profit</th>
                    <th>Margin</th>
                </tr>
                </thead>
                <tbody>
                @foreach($ordersPaginated as $order)
                    @php
                        $textClass = $order['profit'] > 0.5 ? 'text-success' : ($order['profit'] > 0 ? 'text-warning' :'text-danger');
                    @endphp
                    <tr>
                        <td><a href="{{ $order['account']['ebay_link'] }}">{{ $order['account']['username'] }}</a></td>
                        <td><a href="{{ $order['ebay_link'] }}">{{ $order['record'] }}</a></td>
                        <td>{{ usd($order['total']) }}</td>
                        @if ($order['effective'])
                            <td>{{ usd($order['final_value_fee']) }}</td>
                            <td>{{ usd($order['paypal_fee']) }}</td>
                            @if ($order['cog'])
                                <td>{{ usd($order['cog']) }}</td>
                                <td class="{{ $order['cashback'] ? 'text-success' : ''}}">{{ usd($order['cashback']) }}</td>
                                <td class="{{ $textClass }}">{{ usd($order['profit']) }}</td>
                                <td>{{ percent($order['margin']) }}</td>
                            @else
                                <td colspan="4" class="text-muted text-center">Missing Cost of Goods</td>
                            @endif
                        @else
                            <td colspan="6" class="text-muted text-center">Order Canceled</td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </dashboard-orders-table>
    </div>
</div>