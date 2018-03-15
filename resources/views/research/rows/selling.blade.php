@php
    /** @var \DTS\eBaySDK\Shopping\Types\SimpleItemType $item */
@endphp

<tr>
    <td><strong>Sold/Available/Quantity</strong></td>
    @foreach($items as $item)
        <td>{{ $item->QuantitySold }}/{{ $item->Quantity - $item->QuantitySold }}/{{ $item->Quantity }}</td>
    @endforeach
</tr>