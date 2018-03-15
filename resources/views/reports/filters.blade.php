@php
    $days = $endDate->diffInDays($startDate);
    $dateRangeText = "Date Range ({$days} days): {$startDate->toDateString()} – {$endDate->toDateString()}"
@endphp

<span>All Accounts ({{ $user->accounts->count() }})</span>
<span>{{ $dateRangeText }}</span>