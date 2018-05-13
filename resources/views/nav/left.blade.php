@auth
    @beta
    <li><a class="nav-link {{ active_on('items*') }}" href="{{ route('items') }}">Listings</a></li>
    <li><a class="nav-link {{ active_on('orders*') }}" href="{{ route('orders') }}">Orders</a></li>
    <li><a class="nav-link {{ active_on('search/seller*') }}" href="{{ route('search.seller') }}">Search Sellers</a></li>
    @endbeta
@endauth