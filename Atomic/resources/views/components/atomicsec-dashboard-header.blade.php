<nav class="navbar navbar-expand navbar-dark bg-primary">
    <a class="sidebar-toggle mr-3" href="#"><i class="fa fa-bars"></i></a>
    <a class="navbar-brand" href="/dashboard">{{ config('app.name', 'Laravel') }}</a>

    <div class="navbar-collapse collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a href="#" class="nav-link"><i class="fa fa-envelope"></i> 5</a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="fa fa-bell"></i> 3</a></li>
            <li class="nav-item dropdown">
                <a href="#" id="dd_user" class="nav-link dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> {{ Auth::user()->name }}</a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dd_user">
                    <a href="{{route('profile.edit')}}" class="dropdown-item">{{ __('Profile') }}</a>
                    <form class="dropdown-item" id="logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <a type="submit" href="{{route('logout')}}" class="dropdown-item text-left" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</a>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>