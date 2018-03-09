<table class="table table-hover">
    <thead>
    <tr>
        <th scope="col"></th>
        <th scope="col">Record <i class="fa fa-caret-down"></i></th>
        <th scope="col">Item</th>
        <th scope="col">Buyer</th>
        <th scope="col">AMZ</th>
        <th scope="col">Total</th>
        <th scope="col">FVF</th>
        <th scope="col">PPF</th>
        <th scope="col">COG</th>
        <th scope="col">Profit</th>
        <th scope="col">Margin</th>
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
            <td scope="row" class="text-right">{{ $order['created_time']->diffForHumans() }}</td>
            <td>
                <a href="{{ $order['ebay_link'] }}"> {{ $order['record'] }}</a>
                @unless(request('seller'))
                    (<a href="{{ route('orders') }}?seller={{ $order['account']['username'] }}">
                        {{ $order['account']['username'] }}
                    </a>)
                @endif
            </td>
            <td>
                (<span class="text-decoration">{{ $transaction['item_id'] }}</span>)
                <a href="{{ $item['ebay_link'] }}">{{ $transaction['item_title'] }}</a>
            </td>
            <td><a href="{{ $order['buyer_ebay_link'] }}">{{ $order['buyer_username'] }}</a></td>
            <td>
                <i class="fa fa-amazon"></i>
                <a target="_blank" href="{{ $item['associate_link'] }}">{{ $item['sku'] }}</a>
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
            <td>{{ percent($order['margin']) }}</td>
            <td class="text-right">
                @if ($item['cashback_link'])
                    <a class="btn btn-primary btn-sm" target="_blank" href="{{ $item['cashback_link'] }}">
                        Place Order
                    </a>
                @else
                    <span class="text-muted">Can Not Fetch</span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>