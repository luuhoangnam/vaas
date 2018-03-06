@beta
<li><a class="nav-link {{ request()->is('research*') ? 'active' : '' }}" href="#">Research</a></li>
<li><a class="nav-link {{ request()->is('listings*') ? 'active' : '' }}" href="#">Listings</a></li>
<li><a class="nav-link {{ request()->is('orders*') ? 'active' : '' }}" href="{{ route('orders') }}">Orders</a></li>
<li><a class="nav-link {{ request()->is('feedbacks*') ? 'active' : '' }}" href="#">Feedbacks</a></li>
<li><a class="nav-link {{ request()->is('returns*') ? 'active' : '' }}" href="#">Returns</a></li>
<li><a class="nav-link {{ request()->is('automation*') ? 'active' : '' }}" href="#">Automation</a></li>
@endbeta