<!-- Authentication Links -->
@guest
    <li><a class="nav-link" href="{{ route('login') }}">Login</a></li>
    <li><a class="nav-link" href="{{ route('register') }}">Register</a></li>
@else
    <li><a class="nav-link {{ active_on('listings/builder*') }}" href="{{ route('listings.builder.start') }}">Listing Builder</a></li>
    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{ Auth::user()->name }} <span class="caret"></span>
        </a>

        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            @include('snippets.logout-link', ['class' => 'dropdown-item'])
        </div>
    </li>
@endguest