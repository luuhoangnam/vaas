@php
    $textClass = isset($positive) ? ($positive ? 'text-success' : 'text-danger') : (isset($change) && $change > 0 ? 'text-success' : 'text-danger' );
    $arrowDirection = @$direction ?: (isset($change) && $change > 0 ? 'up' : 'down' );
@endphp

<div class="col">
    <div class="card-text align-content-center statistic">
        <h6 class="text-center text-uppercase">{{ $title }}</h6>
        <h2 class="text-center">
            {{ $number }}
            @isset($change)
                @include('dashboard.change', compact('textClass', 'arrowDirection', 'change'))
            @endisset
        </h2>
    </div>
</div>