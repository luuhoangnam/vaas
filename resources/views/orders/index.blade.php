@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 col-xl-1">
                @include('asides.accounts', ['accounts' => $user['accounts']])
            </div>

            <div class="col-xl-11 col-lg-10 col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Orders ({{ $orders->total() }})</span>

                        <a href="#" class="btn btn-sm"><i class="fa fa-refresh"></i> Refresh</a>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="table-responsive-xl">

                        @include('orders.table')

                        {{ $orders->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
