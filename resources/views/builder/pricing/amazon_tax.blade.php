@php
    $tax = old('amazon_tax', 9);
@endphp

<div class="form-group col-md-2">
    <label class="">Tax</label>

    <div class="input-group mb-3">
        {{--<input class="form-control" type="number" step="0.01" max="100" min="0" value="{{ $tax }}">--}}
        <div class="input-group-prepend">
            <div class="input-group-text">
                <input type="checkbox" aria-label="Tax" name="tax" v-model="tax_applied">
            </div>
        </div>

        <div class="input-group-append">
            <span class="input-group-text">Tax Applied</span>
        </div>
    </div>
</div>