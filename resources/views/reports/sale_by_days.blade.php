@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- FILTERS -->
        <div class="row">
            <div class="col-xl-12 d-flex justify-content-between">

                @include('reports.filters')

            </div>
        </div>

        <div class="row">
            <div class="col-xl-2">
                @include('reports.chooser')
            </div>

            <div class="col-xl-10">
                <div class="row">
                    <div class="col-xl-12">
                        @include('reports.chart')
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        @include('reports.overview')
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">

                            <div class="card-header">Report</div>

                            <div class="table-responsive-xl">

                                @include('reports.table', ['aggregator' => 'date'])

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
