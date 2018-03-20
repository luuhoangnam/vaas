@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">Result ({{ $products->count() }})</div>

                    @if (session('status'))
                        <div class="card-body">
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>ASIN</th>
                            <th>Picture</th>
                            <th>Title</th>
                            <th>Prime</th>
                            <th>BO Price</th>
                            <th>BO Seller</th>
                            <th>N&P Offers</th>
                            <th>Lised On</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <a href="{{ route('research.asin', $product['asin']) }}">{{ $product['asin'] }}</a>
                                </td>
                                <td>
                                    @isset($product['images'][0])
                                        <img src="{{ @$product['images'][0]}}"
                                             style="max-height: 5rem; max-width: 5rem">
                                    @else
                                        <span class="text-muted">No Image</span>
                                    @endisset
                                </td>
                                <td>{{ $product['title'] }}</td>
                                <td class="{{ $product['best_offer']['prime'] ? 'text-success' : '' }}">
                                    {{ $product['best_offer']['prime'] ? 'YES' : 'NO' }}
                                </td>
                                <td>{{ usd($product['price']) }}</td>
                                <td>
                                    @isset($product['best_offer'])
                                        {{ $product['best_offer']['seller'] }}
                                    @else
                                        <span class="text-muted">No Offers</span>
                                    @endisset
                                </td>
                                <td>{{ count($product['offers']) }}</td>
                                <td>
                                    @if(count($product['listed_on']))
                                        <ul style="padding-left: 0">
                                            @foreach($product['listed_on'] as $account)
                                                <li>{{ $account }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">Not Listed Yet</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
