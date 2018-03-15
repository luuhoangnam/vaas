@php
    $reportTypes = [
        ['name' => 'Sale by Days', 'route' => 'reports.by_days', 'active_path' => 'reports/by_days*'],
        ['name' => 'Sale by Weeks', 'route' => 'reports.by_weeks', 'active_path' => 'reports/by_weeks*'],
        ['name' => 'Sale by Months', 'route' => 'reports.by_months', 'active_path' => 'reports/by_months*'],
        ['name' => 'Sale by Year', 'route' => 'reports.by_years', 'active_path' => 'reports/by_years*'],
    ];
@endphp

<div class="list-group">
    @foreach($reportTypes as $reportType)
        <a href="{{ route($reportType['route']) }}" class="list-group-item list-group-item-action {{ active_on($reportType['active_path']) }}">
            {{ $reportType['name'] }}
        </a>
    @endforeach
</div>