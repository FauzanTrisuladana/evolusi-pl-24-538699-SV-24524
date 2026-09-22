<aside class="d-flex flex-column p-3 text-white" id="sidebarMenu">
    <!-- Close Button (Mobile Only) -->
    <button id="sidebarCloseBtn" type="button" class="btn btn-sm d-lg-none align-self-end mb-2" aria-label="Close Sidebar">
        <span aria-hidden="true">&times;</span>
    </button>
    <!-- Logo / Brand -->
    <a href="{{ route('links') }}" class="d-flex align-items-center mb-4 text-white text-decoration-none">
        <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
            <span style="font-size: 20px;">🔗</span>
        </div>
        <span class="sidebar-brand">Link Shortener</span>
    </a>

    <hr>

    <!-- Navigation Menu -->
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('links') }}" class="nav-link text-white {{ request()->routeIs('links*') ? 'active' : '' }}" aria-current="page">
                <i class="bi bi-link-45deg me-2"></i>
                Links
            </a>
        </li>
        @if(Route::has('dashboard') && Route::currentRouteName() !== 'links')
        <li>
            <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill me-2"></i>
                Dashboard
            </a>
        </li>
        @endif
        @if(Route::has('analytics'))
        <li>
            <a href="{{ route('analytics') }}" class="nav-link text-white {{ request()->routeIs('analytics') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill me-2"></i>
                Analytics
            </a>
        </li>
        @endif
        @auth
        <li class="mt-3">
            <hr>
        </li>
        <li>
            <a href="{{ Route::has('profile') ? route('profile') : '#' }}" class="nav-link text-white {{ request()->routeIs('profile') ? 'active' : '' }}">
                <i class="bi bi-person-circle me-2"></i>
                Profile
            </a>
        </li>
        @endauth
    </ul>

    <!-- User Profile at Bottom -->
    <hr>
    @auth
    <div class="dropdown">
        <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
            @if(auth()->user()->profile_image ?? false)
                @if(Str::startsWith(auth()->user()->profile_image, ['http://', 'https://']))
                    <img src="{{ auth()->user()->profile_image }}" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                @else
                    <img src="{{ asset(auth()->user()->profile_image) }}" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                @endif
            @else
                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-2 sidebar-user-avatar">
                    {{ strtoupper(collect(explode(' ', auth()->user()->name ?? auth()->user()->username ?? 'U'))->map(fn($n) => substr($n,0,1))->join('')) }}
                </div>
            @endif
            <div>
                <strong class="sidebar-user-name">{{ auth()->user()->name ?? auth()->user()->username }}</strong>
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark w-100">
            @if(Route::has('profile'))
            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            @endif
            <li>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
    @else
    <div class="d-flex flex-column gap-2">
        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm w-100">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login
        </a>
        <a href="{{ route('register') }}" class="btn btn-danger btn-sm w-100">
            <i class="bi bi-person-plus me-1"></i> Register
        </a>
    </div>
    @endauth
</aside>
