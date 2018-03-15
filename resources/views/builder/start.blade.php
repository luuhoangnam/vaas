@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Listing Builder</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="get" action="{{ route('listings.builder.customize') }}">
                            <div class="form-group">
                                <label class="">eBay Account</label>
                                <select class="form-control" name="account" value="{{ old('account') }}">
                                    @foreach($accounts as $account)
                                        <option value="{{ $account['username'] }}">{{ $account['username'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="">Store</label>
                                <select class="form-control" name="source" value="{{ old('source') }}">
                                    @foreach($sources as $source)
                                        <option value="{{ $source }}">{{ $source }}</option>
                                    @endforeach
                                </select>
                                {{--<small class="form-text text-muted">We'll never share your email with anyone else.</small>--}}
                            </div>

                            <div class="form-group">
                                <label class="">Product ASIN/ID</label>
                                <input type="text" class="form-control" name="product_id" placeholder=""
                                       value="{{ old('product_id') }}">
                                {{--<small class="form-text text-muted">We'll never share your email with anyone else.</small>--}}
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Start</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
