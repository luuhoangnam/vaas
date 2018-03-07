@php
    /** @var \DTS\eBaySDK\Shopping\Types\SimpleItemType $item */
@endphp

<tr>
    <td><strong>Title</strong></td>
    @foreach($items as $item)
        <td>
            <a href="{{ seller_url($item->Seller) }}">{{ $item->Seller->UserID }}</a>
            ({{ number_format($item->Seller->FeedbackScore) }})
        </td>
    @endforeach
</tr>