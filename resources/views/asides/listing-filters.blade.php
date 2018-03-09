@php
    $startBefore = old('start_before', request('start_before'));
@endphp

<div class="card">
    <div class="card-header">Filters</div>

    <div class="card-body">
        <form method="get" action="{{ route('items') }}">
            <div class="form-group">
                <label class="">Account</label>
                <select class="form-control" name="account">
                    <option value="" {{ request('account') == false ? 'selected' : '' }}>All</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account['username'] }}" {{ request('account') == $account['username'] ? 'selected' : '' }}>
                            {{ $account['username'] }}
                        </option>
                    @endforeach
                </select>
                {{--<small class="form-text text-muted">We'll never share your email with anyone else.</small>--}}
            </div>

            <div class="form-group">
                <label class="">Listing Status</label>
                <select class="form-control" name="status">
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Ended</option>
                </select>
            </div>

            <div class="form-group">
                <label>Has Sale?</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="has_sale"
                           value="any" {{ request('has_sale') == 'any' || request('has_sale') == '' ? 'checked' : '' }}>
                    <label class="form-check-label">Any</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="has_sale"
                           value="has" {{ request('has_sale') == 'has' ? 'checked' : '' }}>
                    <label class="form-check-label">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="has_sale"
                           value="doesntHas" {{ request('has_sale') == 'doesntHas' ? 'checked' : ''}}>
                    <label class="form-check-label">No</label>
                </div>
            </div>

            <div class="form-group">
                <label>Start Before</label>
                <input type="date" class="form-control" name="start_before"
                       value="{{ $startBefore ? carbon($startBefore)->toDateString() : null }}"/>
            </div>

            <div class="form-group">
                <button type="submit" class="form-control btn btn-primary">Apply Filter</button>
            </div>
        </form>
    </div>

</div>