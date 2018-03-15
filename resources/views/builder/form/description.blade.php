@php
    $template = old('description') ?: $templates->first()->content;
@endphp

<div class="form-group">
    <label class="">Description</label>
    <textarea class="form-control" name="description" rows="10" v-model="initial.template" v-pre></textarea>
    <small class="form-text text-muted">Available variables: title, imageUrl, description.</small>
</div>