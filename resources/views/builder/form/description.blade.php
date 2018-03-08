<div class="form-group">
    <label class="">Description</label>
    <textarea class="form-control" name="description" rows="5">{{ $product['title'] ?: old('title') }}</textarea>
    @php
        $variables = htmlspecialchars("{{ name }}");
    @endphp
    <small class="form-text text-muted">Available variables: {{ $variables }}.</small>
</div>