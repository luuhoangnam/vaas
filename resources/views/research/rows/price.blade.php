@php
    /** @var \DTS\eBaySDK\Shopping\Types\SimpleItemType $item */
@endphp

<tr>
    <td><strong>Price</strong></td>

    @foreach($items as $item)
        <td class="">
            {{ usd($item->CurrentPrice->value) }}
        </td>
    @endforeach
</tr>