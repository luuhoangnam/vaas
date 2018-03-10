@php
    $days = $endDate->diffInDays($startDate);
    $dateRangeText = "Date Range ({$days} days): {$startDate->toDateString()} – {$endDate->toDateString()} (vs. {$previousPeriodStartDate->toDateString()} – {$previousPeriodEndDate->toDateString()})"
@endphp

<span>All Accounts ({{ $user->accounts->count() }})</span>
<span>{{ $dateRangeText }}</span>