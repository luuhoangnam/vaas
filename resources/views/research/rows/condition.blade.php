@php
    /** @var \DTS\eBaySDK\Shopping\Types\SimpleItemType $item */
@endphp

<tr>
    <td><strong>Condition</strong></td>
    @foreach($items as $item)
        <td>{{ $item->ConditionDisplayName }}</td>
    @endforeach
</tr>