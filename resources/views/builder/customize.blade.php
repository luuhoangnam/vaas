@extends('layouts.app')

@php
    $template = $templates->first()->content;
@endphp

@section('content')
    <listing-builder :template="{{ json_encode($template) }}" :product="{{ json_encode($product) }}" inline-template>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-6 col-md-12">
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

                                        @include('builder.form.pricing')

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 d-flex justify-content-center">
                                <button type="submit" class="btn btn-lg btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-xl-6 d-xl-block d-none">
                    <div class="card">
                        <div class="card-header">Preview</div>
                        <div class="card-body">
                            @include('builder.preview')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </listing-builder>
@endsection
