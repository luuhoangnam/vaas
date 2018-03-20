@extends('layouts.app')

@php
    /** @var \DTS\eBaySDK\Finding\Types\FindItemsByKeywordsResponse $competitors */
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="card">
                    <div class="card-header">
                        (<a href="https://www.amazon.com/dp/{{ $product['asin'] }}">{{ $product['asin'] }}</a>)
                        {{ str_limit($product['title'], 150) }}
                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="row">
                                    @foreach($product['images'] as $image)
                                        <div class="col text-center">
                                            <img class="text-center" src="{{ $image }}"
                                                 style="max-height: 12rem; max-width: 100%; border:2px solid #efefef">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col">
                                        <div class="card-text align-content-center statistic">
                                            <h6 class="text-center text-uppercase">Price</h6>
                                            <h2 class="text-center">{{ usd($product['best_offer']['price']) }}</h2>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="card-text align-content-center statistic">
                                            <h6 class="text-center text-uppercase">Tax</h6>
                                            <h2 class="text-center {{ $product['best_offer']['tax'] ? '' : 'text-success' }}">
                                                {{ $product['best_offer']['tax'] ? 'YES' : 'NO' }}
                                            </h2>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="card-text align-content-center statistic">
                                            <h6 class="text-center text-uppercase">Cost of Goods</h6>
                                            <h2 class="text-center">{{ usd($product['best_offer']['price']) }}</h2>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="card-text align-content-center statistic">
                                            <h6 class="text-center text-uppercase">Selling Price</h6>
                                            <h2 class="text-center"> >={{ usd($minSellingPrice) }}</h2>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="card-text align-content-center statistic">
                                            <h6 class="text-center text-uppercase">Prime</h6>
                                            <h2 class="text-center {{ $product['best_offer']['prime'] ? 'text-success' : '' }}">
                                                {{ $product['best_offer']['prime'] ? 'YES' : 'NO' }}
                                            </h2>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="card-text align-content-center statistic">
                                            <h6 class="text-center text-uppercase">Seller</h6>
                                            <h2 class="text-center">{{ $product['best_offer']['seller'] }}</h2>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="card-text align-content-center statistic">
                                            <h6 class="text-center text-uppercase">Listed On</h6>
                                            @if(count($product['listed_on']))
                                                <ul style="padding-left: 0">
                                                    @foreach($product['listed_on'] as $account)
                                                        <li>{{ $account }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted text-center">Not Listed Yet</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            Competitors {{ $competitors ? "({$competitors->paginationOutput->totalEntries})" : '' }}
                        </span>

                        @if($competitors)
                            <span>
                            On This Page => Higher Price: {{ number_format($higherPrice) }}
                                | Equals Price: {{ number_format($equalsPrice) }}
                                | Lower Price: {{ number_format($lowerPrice) }}
                        </span>
                        @endif
                    </div>

                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Picture</th>
                            <th>Title</th>
                            <th>Seller</th>
                            <th>Price</th>
                            <th>ASIN</th>
                            <th>Sold 30D</th>
                            <th>Listed</th>
                        </tr>
                        </thead>
                        <tbody>
                        @unless($competitors)
                            <tr>
                                <td colspan="6" class="text-muted text-center">No Competitors</td>
                            </tr>
                        @else
                            @foreach($competitors->searchResult->item as $index => $item)
                                @php
                                    $price = $item->sellingStatus->currentPrice->value;
                                    $priceDiff = $price - $minSellingPrice;
                                    $soldLast30D = !is_null($soldLastThirtyDays[$item->itemId]) ? number_format($soldLastThirtyDays[$item->itemId]): 'N/A';
                                    $asin = @$skus[$item->itemId] ?: 'N/A';
                                @endphp

                                <tr>
                                    <td>{{ $index + 1 }}.</td>
                                    <td>
                                        <img src="{{ $item->galleryURL }}" style="max-width: 3rem; max-height: 3rem;">
                                    </td>
                                    <td>({{ $item->itemId }}) {{ $item->title }}</td>
                                    <td>
                                        {{ $item->sellerInfo->sellerUserName }}
                                        ({{ number_format($item->sellerInfo->feedbackScore) }})
                                    </td>
                                    <td class="{{ $priceDiff > 0 ? 'text-success' : ($priceDiff < 0 ? 'text-danger' : '') }}">
                                        {{ usd($price) }}
                                        <small>({{ percent($priceDiff / $minSellingPrice) }})</small>
                                    </td>
                                    <td class="{{ $asin === $product['asin'] ? 'text-success' : null }}">
                                        {{ $asin }}
                                    </td>
                                    <td class="{{ $soldLast30D >= 10 ? 'text-success' : null }}">
                                        {{ $soldLast30D }}
                                    </td>
                                    <td>{{ app_carbon($item->listingInfo->startTime)->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        @endunless
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
