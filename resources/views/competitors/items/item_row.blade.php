<tr>
    <td>{{ $item['owner']['username'] }}</td>
    <td>
        <a href="https://www.ebay.com/itm/{{ $item['item_id'] }}" target="_blank">
            <img src="{{ $item['picture_url'] }}" alt="{{ $item['title'] }}" style="max-height: 3rem; max-width: 3rem;">
        </a>
    </td>
    <td>
        {{ $item['title'] }}
        (<a class="text-muted" href="https://www.ebay.com/itm/{{ $item['item_id'] }}"
            target="_blank">{{ $item['item_id'] }}</a>)
    </td>
    <td>{{ usd($item['price']) }}</td>
    <td title="{{ $item['start_time']->diffForHumans() }}" data-toggle="tooltip" data-placement="top"
        title="{{ $item['start_time']->diffForHumans() }}">
        {{ $item['start_time']->toDateString() }}
    </td>
    @if($item['sku'])
        <td>{{ $item['sku'] }}</td>
        <td>
            @if($item['listed_on'])
                @foreach($item['listed_on'] as $account)
                    <div>{{ $account['username'] }}</div>
                @endforeach
            @else
                <span class="text-muted text-center">Not Listed</span>
            @endif
        </td>
    @else
        <td colspan="5" class="text-muted text-center">N/A</td>
    @endif
</tr>