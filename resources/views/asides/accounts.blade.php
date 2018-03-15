<div class="card">
    <div class="card-header">Accounts ({{ $accounts->count() }})</div>

    <div class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
        <a class="nav-link {{ request('seller') ? '' : 'active' }}"
           href="{{ route('orders') }}">
            All
        </a>

        @foreach($accounts as $account)
            <a class="nav-link {{ request('seller') === $account['username'] ? 'active' : '' }}"
               href="{{ route('orders') }}?seller={{ $account['username'] }}" target="_self">
                {{ $account['username'] }}
            </a>
        @endforeach
    </div>

</div>