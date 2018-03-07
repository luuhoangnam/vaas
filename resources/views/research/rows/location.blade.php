@php
    /** @var \DTS\eBaySDK\Shopping\Types\SimpleItemType $item */
@endphp

<tr>
    <td><strong>Location</strong></td>
    @foreach($items as $item)
        <td>{{ $item->Location }}, {{ $item->PostalCode }}</td>
    @endforeach
</tr>