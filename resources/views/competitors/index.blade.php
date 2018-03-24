@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-2">
                <div class="card">
                    <div class="card-header">New Competitor</div>
                    <div class="card-body">
                        <form action="{{ route('competitor.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control" name="username" placeholder="Competitor Username" value="{{ old('username') }}">
                            </div>

                            <div class="form-group">
                                <button class="form-control btn btn-primary" type="submit">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-10">
                <div class="card">
                    <div class="card-header">Competitors ({{ $competitors->count() }})</div>

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
                                <th>Username</th>
                                <th>Found Items</th>
                                <th>Feedbacks</th>
                                <th>Sell Through</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($competitors as $competitor)
                                <tr>
                                    <td>{{ $competitor['username'] }}</td>
                                    <td>{{ number_format($competitor['items_count']) }}</td>
                                    <td>{{ number_format($competitor['feedbacks']) }}</td>
                                    <td>{{ percent($competitor['sell_through']) }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        {{ $competitors->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
