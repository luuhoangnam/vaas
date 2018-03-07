@extends('layouts.app')

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
                            <div class="card-header">Performance</div>
                            <div class="card-body">
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ad adipisci aperiam debitis,
                                ea esse fugit harum, hic illo impedit iusto laborum nam officiis quia sapiente soluta
                                totam ullam unde vitae.
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                @php
                                    $filtered = request()->has('account') || request()->has('status') || request()->has('has_sale');
                                @endphp

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
