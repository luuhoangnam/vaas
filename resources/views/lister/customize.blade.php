@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/trumbowyg/trumbowyg.min.css') }}"/>
@endpush

@section('content')
    <lister inline-template
            :product="{{ json_encode($product) }}"
            :categories="{{ json_encode($categories) }}"
            :profiles="{{ json_encode($profiles) }}">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <form action="{{ route('lister.submit') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">Customize The Listing</div>

                                    <div class="card-body">
                                        <!-- TITLE -->
                                        <div class="form-group">
                                            <label class="">Title</label>
                                            <input class="form-control" type="text" name="title" v-model="title"
                                                   maxlength="80">
                                            <small class="form-text text-right text-muted">@{{ characterLeft }}
                                                character(s) left.
                                            </small>
                                        </div>

                                        <!-- CATEGORY -->
                                        <div class="form-group">
                                            <label class="">Category</label>
                                            <select class="form-control" name="account" value="{{ old('account') }}"
                                                    v-model="primary_category_id">
                                                <option :value="category.id" v-for="category in categories">@{{
                                                    category.breadcrumb }}&nbsp;@{{ category.name }} (@{{
                                                    category.percent }}%)
                                                </option>
                                            </select>
                                        </div>

                                        <!-- QUANTITY -->
                                        <div class="form-group">
                                            <label class="">Quantity</label>
                                            <input class="form-control" type="number" step="1" min="0" name="quantity"
                                                   v-model="quantity">
                                        </div>

                                        <!-- PROFILES -->
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label class="">Payment Profile</label>
                                                <select class="form-control" name="payment_profile_id"
                                                        v-model="payment_profile_id">
                                                    <option :value="profile.ProfileID"
                                                            v-for="profile in profiles['PAYMENT']">@{{
                                                        profile.ProfileName }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label class="">Shipping Profile</label>
                                                <select class="form-control" name="shipping_profile_id"
                                                        v-model="shipping_profile_id">
                                                    <option :value="profile.ProfileID"
                                                            v-for="profile in profiles['SHIPPING']">@{{
                                                        profile.ProfileName }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label class="">Returns Profile</label>
                                                <select class="form-control" name="return_profile_id"
                                                        v-model="returns_profile_id">
                                                    <option :value="profile.ProfileID"
                                                            v-for="profile in profiles['RETURN_POLICY']">@{{
                                                        profile.ProfileName }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="">Description</label>
                                            <textarea id="trumbowyg-editor" class="form-control"
                                                      name="description">{{ old('description', $description) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between">
                                        <span>Pricing</span>
                                        <strong>Selling Price: $@{{ calculatedPrice }}</strong>
                                    </div>

                                    <div class="card-body">

                                        <input type="hidden" value="price" v-model="calculatedPrice">

                                        @include('lister.pricing')

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">Attributes</div>

                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-md-4" v-for="attribute in product.attributes">
                                                <strong>@{{ attribute.name }}</strong>: @{{ attribute.value }}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">Images</div>

                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-md-2" v-for="src in product.images">
                                                <img :src="src" class="rounded" width="100%">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group d-flex justify-content-between">
                                    <a href="{{ route('lister.start') }}" class="btn btn-secondary">Back</a>
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </lister>
@endsection
