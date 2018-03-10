<div class="card">
    <div class="card-header">
        New Listings ({{ $newItems->count() }})
        @foreach($newItems->groupBy('account.username') as $username => $accountItems)
            | {{ $username }} ({{ $accountItems->count() }})
        @endforeach
    </div>

    <div class="table-responsive-md">
        <table class="table table-hover">
            <thead>
            <tr>
                <th></th>
                <th>Account</th>
                <th>Title</th>
                <th>Price</th>
            </tr>
            </thead>

            <tbody>
            @foreach($newItemsPaginated as $item)
                <tr>
                    <td>{{ $item['start_time']->diffForHumans() }}</td>
                    <td>{{ $item['account']['username'] }}</td>
                    <td>(<a href="{{ $item['ebay_link'] }}">{{ $item['item_id'] }}</a>)&nbsp;{{ $item['title'] }}</td>
                    <td>{{ usd($item['price']) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>