@extends('layouts.app')

@section('content')
    <div class="container">
        <form method="get" action="{{ route('listings.builder') }}">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Listing Builder</div>

                        <div class="card-body">

                            @include('builder.form.title')
                            @include('builder.form.category')
                            @include('builder.form.description')

                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Pricing</div>

                        <div class="card-body">

                            @include('builder.form.title')

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
