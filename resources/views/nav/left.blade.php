{{--<li><a class="nav-link" href="{{ route('orders') }}">Listings</a></li>--}}
<li><a class="nav-link {{ request()->is('orders*') ? 'active' : '' }}" href="{{ route('orders') }}">Orders</a></li>