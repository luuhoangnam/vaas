@extends('layouts.app')

@section('content')
    <dashboard :data="{{ json_encode($saleChart) }}" inline-template>
        <div class="container-fluid">

            <!-- FILTERS -->
            <div class="row">
                <div class="col-xl-12 d-flex justify-content-between">
                    <span>All Accounts ({{ $user->accounts->count() }})</span>
                    <span>This Week ({{ $startDate->toDateString() }} – {{ $endDate->toDateString() }})</span>
                </div>
            </div>

            <!-- PERFORMANCE METRICS -->
            <div class="row">
                <div class="col-xl-6">
                    @include('dashboard.overview')
                </div>

                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">Sale Chart</div>

                        <div class="card-body">
                            <canvas id="sale-chart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dashboard>
@endsection
