@php
    /** @var \DTS\eBaySDK\Shopping\Types\SimpleItemType $item */
@endphp

<tr>
    <td><strong>Picture</strong></td>
    @foreach($items as $item)
        <td>
            <img class="rounded mx-auto d-block"
                 src="{{ $item->PictureURL[0] }}"
                 alt="{{ $item->Title }}"
                 width="200" height="200">
        </td>
    @endforeach
</tr>