<tr>
    <td><strong>Title</strong></td>
    @foreach($items as $item)
        <td>{{ $item->Title }}</td>
    @endforeach
</tr>