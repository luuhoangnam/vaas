@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">Filter</div>

                    <div class="card-body">
                        <form method="GET">

                            <div class="form-row">

                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">Competitor Items ({{ $items->count() }})</div>

                    @if (session('status'))
                        <div class="card-body">
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">

                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Competitor</th>
                                <th>Picture</th>
                                <th>Title</th>
                                <th>Price</th>
                                <th>Start Time</th>
                                <th>SKU</th>
                                <th>Cost</th>
                                <th>Seller</th>
                                <th>Profit</th>
                                <th>Listed On</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)

                                @include('competitors.items.item_row')

                            @endforeach
                            </tbody>
                        </table>

                        {{ $items->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
