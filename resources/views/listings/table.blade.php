<table class="table table-hover">
    <thead>
    <tr>
        <th scope="col"></th>
        <th scope="col">Title</th>
        <th scope="col">AMZ</th>
        <th scope="col">Price</th>
        <th scope="col">Orders</th>
        <th scope="col">Earning</th>
        <th scope="col"></th>
    </tr>
    </thead>

    <tbody>
    @foreach($items as $item)
        @php
            //
        @endphp

        <tr>
            <td scope="row" class="text-right">{{ $item['start_time']->diffForHumans() }}</td>
            <td>(<a href="{{ $item['ebay_link'] }}">{{ $item['item_id'] }}</a>)&nbsp;{{ $item['title'] }}</td>
            <td>
                @if($item['sku'])
                    @include('snippets.item.associate-link', compact('item'))
                @endif
            </td>
            <td>{{ usd($item['price']) }}</td>
            <td>{{ $item['orders_count'] }}</td>
            <td>{{ usd($item['earning']) }}</td>
            <td class="text-right">
                {{--Actions--}}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>