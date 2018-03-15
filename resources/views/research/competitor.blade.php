@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-md-12">
                <div class="card">
                    <div class="card-header">Competitor Research</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form action="{{ route('research.competitor') }}">
                            <div class="form-row">
                                <div class="form-group col-md-9">
                                    <input class="form-control" name="username" value="{{ old('username') }}"
                                           placeholder="Username">
                                </div>

                                <div class="form-group col-md-2">
                                    <select class="form-control" name="date_range" value="{{ old('date_range') }}">
                                        <option value="7">7 Days</option>
                                        <option value="14">14 Days</option>
                                        <option value="30" selected>30 Days</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-1">
                                    <button class="form-control btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @isset($performance)
            <div class="row justify-content-center">
                <div class="col-xl-6 col-md-12">
                    <div class="card">
                        <div class="card-header">Seller Performance</div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="card-text align-content-center statistic">
                                        <h6 class="text-center text-uppercase">Sell-Through</h6>
                                        <h2 class="text-center">
                                            {{ percent($performance['sell_through']) }}
                                        </h2>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="card-text align-content-center statistic">
                                        <h6 class="text-center text-uppercase">Active Listings</h6>
                                        <h2 class="text-center">
                                            {{ number_format($performance['active_listings']) }}
                                        </h2>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="card-text align-content-center statistic">
                                        <h6 class="text-center text-uppercase">Sold Items</h6>
                                        <h2 class="text-center">
                                            {{ number_format($performance['sold_items']) }}
                                        </h2>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="card-text align-content-center statistic">
                                        <h6 class="text-center text-uppercase">Sale Earning</h6>
                                        <h2 class="text-center">
                                            {{ usd($performance['sale_earning']) }}
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endisset

        @isset($items)
            <div class="row justify-content-center">
                <div class="col-xl-6 col-md-12">
                    <div class="card">
                        <div class="card-header">Sold Items</div>


                    </div>
                </div>
            </div>
        @endisset
    </div>
@endsection
