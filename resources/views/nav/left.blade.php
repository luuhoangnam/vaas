@auth
    @beta
    <li><a class="nav-link {{ active_on('research*') }}" href="#">Research</a></li>
    <li><a class="nav-link {{ active_on('items*') }}" href="{{ route('items') }}">Listings</a></li>
    <li><a class="nav-link {{ active_on('orders*') }}" href="{{ route('orders') }}">Orders</a></li>
    <li><a class="nav-link {{ active_on('feedbacks*') }}" href="#">Feedbacks</a></li>
    <li><a class="nav-link {{ active_on('returns*') }}" href="#">Returns</a></li>
    <li><a class="nav-link {{ active_on('automation*') }}" href="#">Automation</a></li>
    <li><a class="nav-link {{ active_on('reports*') }}" href="{{ route('reports') }}">Reports</a></li>
    @endbeta
@endauth