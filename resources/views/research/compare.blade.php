@extends('layouts.app')

@php
    /** @var \DTS\eBaySDK\Shopping\Types\GetMultipleItemsResponseType|array $items */
    /** @var \DTS\eBaySDK\Shopping\Types\SimpleItemType $item */
@endphp

@section('content')
    <div class="container{{ count($items) > 4? '-fluid' : '' }}">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">Compare {{ count($items) }} items</div>

                    @if(count($errors))
                        <div class="card-body">
                            @foreach($errors as $error)
                                <div class="alert alert-{{ severity_code_to_class($error->SeverityCode) }}">
                                    {{ $error->LongMessage }}
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="table-responsive-xl">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <td scope="col"></td>
                                @foreach($items as $index => $item)
                                    <th scope="col">Item #{{ $index++ }}</th>
                                @endforeach
                            </tr>
                            </thead>

                            <tbody>
                            @include('research.rows.item_id', ['firstRow' => true])
                            @include('research.rows.picture')
                            @include('research.rows.title')
                            @include('research.rows.seller')
                            @include('research.rows.price')
                            @include('research.rows.location')
                            @include('research.rows.selling')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
