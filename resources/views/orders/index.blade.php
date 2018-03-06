@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 col-xl-1">
                <div class="card">
                    <div class="card-header">Accounts ({{ $user['accounts']->count() }})</div>

                    <div class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
                        <a class="nav-link {{ is_null($activeSeller) ? 'active' : '' }}"
                           href="{{ route('orders') }}">
                            All
                        </a>

                        @foreach($user['accounts'] as $account)
                            <a class="nav-link {{ $activeSeller === $account['username'] ? 'active' : '' }}"
                               href="{{ route('orders') }}?seller={{ $account['username'] }}" target="_self">
                                {{ $account['username'] }}
                            </a>
                        @endforeach
                    </div>

                </div>
            </div>

            <div class="col-xl-11 col-lg-10 col-md-9">
                <div class="card">
                    <div class="card-header">Orders ({{ $orders->total() }})</div>

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
