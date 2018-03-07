<tr>
    <td {{ @$firstRow ? 'scope="row"' : '' }}><strong>Item ID</strong></td>
    @foreach($items as $item)
        <td>
            <a href="{{ item_url($item) }}">{{ $item->ItemID }}</a>
        </td>
    @endforeach
</tr>