<table class="table table-hover">
    <caption>
        &nbsp;Legend: TTFFS = Time Took for First Sale (Since start time of an item until it has its first order)
    </caption>
    <thead>
    <tr>
        <th scope="col"></th>
        <th scope="col">Title</th>
        <th scope="col">Source</th>
        <th scope="col">Price</th>
        @unless(request('has_sale') == 'doesntHas')
            <th scope="col">Orders</th>
            <th scope="col">Earning</th>
            <th scope="col">TTFFS</th>
        @endunless
        <th scope="col"></th>
    </tr>
    </thead>

    <tbody>
    @foreach($items as $item)
        <tr>
            <td scope="row" class="text-right">{{ $item['start_time']->diffForHumans() }}</td>
            <td>(<a href="{{ $item['ebay_link'] }}">{{ $item['item_id'] }}</a>)&nbsp;{{ $item['title'] }}</td>
            <td>
                @if($item['sku'])
                    @include('snippets.item.associate-link', compact('item'))
                @endif
            </td>
            <td>{{ usd($item['price']) }}</td>
            @unless(request('has_sale') == 'doesntHas')
                <td>{{ $item['orders_count'] }}</td>
                <td>{{ usd($item['earning']) }}</td>
                <td>{{ $item['time_took_for_first_sale'] }}</td>
            @endunless
            <td class="text-right">
                {{--Actions--}}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>