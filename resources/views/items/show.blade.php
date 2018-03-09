@extends('layouts.app')

@php
    $sourcePrice = $item['source_price'];
    $profit = $item['price'] - $item['price'] * (0.0915 + 0.039) - 0.3 - $sourcePrice * 1.09;
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-6">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            (<a href="{{ $item['ebay_link'] }}" class="text-muted">{{ $item['item_id'] }}</a>)
                            {{ $item['title'] }}
                        </span>

                        <span>
                            <a class="text-" href="{{ $item['ebay_link'] }}">View on eBay</a>
                        </span>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-xl-3">
                                <img class="img-fluid" src="{{ $item['picture_url'] }}"/>
                            </div>
                            <div class="col-xl-9">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <p><strong>Current Price:</strong>&nbsp;{{ usd($item['price']) }}</p>
                                        <p><strong>Available
                                                Quantity:</strong>&nbsp;{{ number_format($item['quantity_available']) }}
                                        </p>
                                        <p><strong>Sold Quantity:</strong>&nbsp;{{ number_format($item['quantity_sold']) }}</p>
                                        <p><strong>Submited Since:</strong>&nbsp;{{ $item['start_time']->diffForHumans() }}</p>
                                    </div>
                                    <div class="col-xl-6">
                                        <p><strong>Source:</strong>&nbsp;<i
                                                    class="fa fa-amazon"></i>&nbsp;{{ $item['sku'] }}</p>
                                        <p><strong>Cost:</strong>&nbsp;{{ usd($sourcePrice) }}</p>
                                        <p><strong>Profit:</strong>&nbsp;{{ usd($profit) }}</p>
                                        <p><strong>Margin:</strong>&nbsp;{{ percent($profit / $item['price']) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
            <div class="col-xl-6">

                <item-chart :config="{{ json_encode($chart) }}" inline-template>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            Sale Performance Chart
                        </div>
                        <div class="card-body">

                            <canvas id="item-chart" height="100"></canvas>

                        </div>
                    </div>
                </item-chart>

            </div>
        </div>
    </div>
@endsection
