@extends('layouts.app')

@php
    $filtered = request()->has('account') || request()->has('status') || request()->has('has_sale');
@endphp

@section('content')
    <div class="container-fluid" xmlns:v-bind="http://www.w3.org/1999/xhtml">
        <div class="row">
            <div class="col-md-4 col-lg-3 col-xl-2">
                @include('asides.listing-filters', ['accounts' => $user['accounts']])
            </div>

            <div class="col-xl-10 col-lg-9 col-md-8">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">

                        @include('listings.kpi')

                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <items-table :items="{{ json_encode($allItems->toArray()) }}" inline-template>
                            <div class="card">
                                <div id="ids" class="d-none" v-text="ids.join('\n')"></div>

                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>
                                        @if($filtered)
                                            Filtered Items ({{ $items->total() }})
                                        @else
                                            Items ({{ $items->total() }})
                                        @endif
                                    </span>

                                    <span>
                                        <a id="copyIds" href="#" class="btn btn-sm" :data-clipboard-text="ids.join('\n')" @click="copyIds"><i class="fa fa-copy"></i> Copy IDs</a>
                                        <a href="#" class="btn btn-sm"><i class="fa fa-refresh"></i> Refresh</a>
                                    </span>
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
                        </items-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
