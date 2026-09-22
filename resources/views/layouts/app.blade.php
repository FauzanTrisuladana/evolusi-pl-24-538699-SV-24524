<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Shortener') }} - @yield('title', 'Links')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Bootstrap & Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endif
</head>
<body class="dashboard-layout">
    <!-- Sidebar Component -->
    <x-sidebar />

    <!-- Main Content -->
    <main class="flex-grow-1 dashboard-main">
        <!-- Navbar / Header -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="container-fluid px-4">
                <button class="navbar-toggler d-lg-none" type="button" id="sidebarToggleBtn" aria-label="Toggle Sidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="ms-auto d-flex align-items-center gap-3">
                    <button class="btn btn-light" style="border-radius: 8px;" id="darkModeToggle" onclick="toggleDarkMode()">
                        <i class="bi bi-brightness-high" id="darkModeIcon"></i>
                    </button>

                    @auth
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                @if(auth()->user()->profile_image ?? false)
                                    @if(Str::startsWith(auth()->user()->profile_image, ['http://', 'https://']))
                                        <img src="{{ auth()->user()->profile_image }}" alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset(auth()->user()->profile_image) }}" alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                    @endif
                                @else
                                    <span class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        {{ strtoupper(collect(explode(' ', auth()->user()->name ?? auth()->user()->username ?? 'U'))->map(fn($n) => substr($n,0,1))->join('')) }}
                                    </span>
                                @endif
                                <span class="ms-2 d-none d-md-inline">{{ auth()->user()->name ?? auth()->user()->username }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ Route::has('profile') ? route('profile') : '#' }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
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
                        <div class="d-flex gap-2">
                            <a href="{{ route('login') }}" class="btn btn-outline-danger btn-sm">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-danger btn-sm">Register</a>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid px-4 py-4">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/js/layout-darkmode.js')
    @else
        <script src="{{ asset('js/layout-darkmode.js') }}"></script>
    @endif
    @stack('scripts')
</body>
</html>
