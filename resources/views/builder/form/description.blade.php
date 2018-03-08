@php
    $template = old('description') ?: $templates->first()->content;
@endphp

<div class="form-group">
    <label class="">Description</label>
    <textarea class="form-control" name="description" rows="10">{{ $template }}</textarea>
    <small class="form-text text-muted">Available variables: title, imageUrl, description.</small>
</div>