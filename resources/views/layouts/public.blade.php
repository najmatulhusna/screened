<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SCREENED')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@400;500;600&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('AdminLTE-master/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ filemtime(public_path('css/custom.css')) }}">
</head>

<body class="public-page">

    <header class="public-navbar">
        <div class="public-container">

            <a href="{{ route('collection') }}" class="navbar-brand-wrap">
                <span class="navbar-brand-logo">SCREENED</span>
                @if(session()->has('admin_id'))
                    <span class="navbar-admin-tag">admin</span>
                @endif
            </a>

            <nav class="nav-menu">
                <a href="{{ route('collection') }}"
                    class="{{ request()->routeIs('collection') ? 'active' : '' }}">Collection</a>

                @auth
                    <a href="{{ route('favorites') }}"
                        class="{{ request()->routeIs('favorites') ? 'active' : '' }}">Favorites</a>
                    <a href="{{ route('statistics') }}"
                        class="{{ request()->routeIs('statistics') ? 'active' : '' }}">Statistics</a>
                @endauth
            </nav>

            <div class="nav-menu">
                @if(session()->has('admin_id'))
                    <span class="nav-user">Admin</span>
                    <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link-btn"
                            data-confirm
                            data-title="Logout dari SCREENED?"
                            data-text="Sesi admin kamu akan berakhir dan harus login lagi."
                            data-submit="Ya, Logout">Logout</button>
                    </form>
                @elseif(auth()->check())
                    <span class="nav-user">{{ auth()->user()->username }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link-btn"
                            data-confirm
                            data-title="Logout dari SCREENED?"
                            data-text="Kamu akan keluar dan harus login lagi untuk menyimpan progres."
                            data-submit="Ya, Logout">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Login</a>
                @endif
            </div>

        </div>
    </header>


    <main class="app-content public-content">
        <div class="public-container">

            @if(session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger py-2">{{ session('error') }}</div>
            @endif

            @yield('content')

        </div>
    </main>

    @include('partials.confirm-modal')

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
    <script src="{{ asset('AdminLTE-master/dist/js/adminlte.min.js') }}"></script>

    @stack('scripts')
</body>
</html>
