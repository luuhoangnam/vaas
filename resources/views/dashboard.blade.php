@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- FILTERS -->
        <div class="row">
            <div class="col-xl-12 d-flex justify-content-between">

                @include('dashboard.filters')

            </div>
        </div>

        <!-- PERFORMANCE METRICS -->
        <div class="row">
            <div class="col-xl-6">
                <div class="row">
                    <div class="col-xl-12">
                        @include('dashboard.overview')
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">

                        @include('dashboard.new-orders')

                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="row">
                    <div class="col-xl-12">

                        @include('dashboard.sale-chart')

                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">

                        @include('dashboard.price-distribution')

                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">

                        @include('dashboard.new-items')

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
