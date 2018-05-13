@extends('layouts.app')

@section('content')
    <search-seller inline-template>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card">
                        <div class="card-header">Product Research</div>

                        <div class="card-body">

                            <form action="{{ route('search.seller') }}" method="GET">
                                <div class="form-row">
                                    <div class="col-6">
                                        <input type="text" class="form-control" placeholder="Keyword" required
                                               value="{{ old('keyword', request('keyword')) }}"
                                               name="keyword">
                                    </div>
                                    <div class="col">
                                        <input type="number" step="0.01" class="form-control" placeholder="Min. Price"
                                               value="{{ old('min_price', request('min_price')) }}"
                                               name="min_price">
                                    </div>
                                    <div class="col">
                                        <input type="number" step="1" class="form-control" placeholder="Min. Feedback"
                                               value="{{ old('min_feedback', request('min_feedback')) }}"
                                               name="min_feedback">
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-primary">Search</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            @isset($sellers)
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>{{ $sellers->count() }} {{ str_plural('Seller', $sellers->count()) }}</span>
                                <span>
                                    <a class="btn btn-sm btn-outline-primary" href="#" @click.prevent="hideResearched = !hideResearched">
                                        <span v-if="!hideResearched">Hide</span><span v-else>Show</span> Researched
                                    </a>
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Seller</th>
                                        <th>Feedback Score</th>
                                        <th>Feedback Rate</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($sellers as $seller)
                                        <tr v-if="!isHidden('{{ $seller['username'] }}')"
                                            :class="{'table-warning':isResearched('{{ $seller['username'] }}')}">
                                            <td>
                                                <a href="{{ $seller['url'] }}">
                                                    {{ $seller['username'] }}
                                                </a>
                                            </td>
                                            <td>{{ number_format($seller['feedback_score']) }}</td>
                                            <td>{{ $seller['feedback_rate'] }}%</td>
                                            <td class="text-right">
                                                <a href="{{ $seller['zik'] }}" class="btn btn-sm btn-outline-primary"
                                                   target="_blank">
                                                    Zik →
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endisset
        </div>
    </search-seller>
@endsection
