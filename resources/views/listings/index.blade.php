@extends('layouts.app')

@php
    $filtered = request()->has('account') || request()->has('status') || request()->has('has_sale');
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-3 col-xl-2">
                @include('asides.listing-filters', ['accounts' => $user['accounts']])
            </div>

            <div class="col-xl-10 col-lg-9 col-md-8">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Performance {{ $filtered ? ' on Filtered Items' : '' }}</span>
                                <span class="text-muted text-right">
                                    EPI: <strong>E</strong>arning <strong>P</strong>er <strong>I</strong>tem
                                    |
                                    OPI: <strong>O</strong>rders <strong>P</strong>er <strong>I</strong>tem
                                    |
                                    AIV: <strong>A</strong>verage <strong>I</strong>tem <strong>V</strong>alue
                                    |
                                    STR: <strong>S</strong>ale <strong>T</strong>hrough <strong>R</strong>ate
                                </span>
                            </div>
                            <div class="card-body">

                                <div class="row">
                                    <div class="col">
                                        <div class="card-text align-content-center">
                                            <h6 class="text-center text-uppercase">Earning</h6>
                                            <h2 class="text-center">{{ usd($totalEarning) }}</h2>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card-text align-content-center">
                                            <h6 class="text-center text-uppercase">Items</h6>
                                            <h2 class="text-center">{{ number_format($items->total()) }}</h2>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card-text align-content-center">
                                            <h6 class="text-center text-uppercase">Orders</h6>
                                            <h2 class="text-center">{{ number_format($totalOrders) }}</h2>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card-text align-content-center">
                                            <h6 class="text-center text-uppercase">EPI</h6>
                                            <h2 class="text-center">{{ usd($earningPerItem) }}</h2>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card-text align-content-center">
                                            <h6 class="text-center text-uppercase">AOV</h6>
                                            <h2 class="text-center">{{ usd($totalOrders ? $totalEarning / $totalOrders : 0) }}</h2>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card-text align-content-center">
                                            <h6 class="text-center text-uppercase">OPI</h6>
                                            <h2 class="text-center">{{ number_format($items->total() ? $totalOrders / $items->total() : 0, 2) }}</h2>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card-text align-content-center">
                                            <h6 class="text-center text-uppercase">AIV</h6>
                                            <h2 class="text-center">{{ usd($items->total() ? $totalItemsValue / $items->total() : 0) }}</h2>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card-text align-content-center">
                                            <h6 class="text-center text-uppercase">STR</h6>
                                            <h2 class="text-center">{{ percent($saleThroughRate) }}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                @if($filtered)
                                    <span>Filtered Items ({{ $items->total() }})</span>
                                @else
                                    <span>Items ({{ $items->total() }})</span>
                                @endif

                                <a href="#" class="btn btn-sm"><i class="fa fa-refresh"></i> Refresh</a>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <div class="table-responsive-xl">

                                @include('listings.table')

                                {{ $items->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
