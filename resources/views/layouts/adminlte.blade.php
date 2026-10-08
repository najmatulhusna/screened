<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trim($__env->yieldContent('title', 'SCREENED')) }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('AdminLTE-master/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ filemtime(public_path('css/custom.css')) }}">
</head>

@php $hasSidebar = auth()->check() || session()->has('admin_id'); @endphp
<body class="{{ $hasSidebar ? 'layout-fixed sidebar-expand-lg' : '' }} bg-body-tertiary">
    <div class="app-wrapper">

        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    @if($hasSidebar)
                        <li class="nav-item">
                            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                                <i class="bi bi-list"></i>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item d-none d-md-block">
                        <a href="{{ route('collection') }}" class="nav-link">
                            <i class="bi bi-film me-1"></i> SCREENED
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto">
                    @if(session()->has('admin_id'))
                        <li class="nav-item"><span class="nav-link">Admin</span></li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link"
                                    data-confirm
                                    data-title="Logout dari SCREENED?"
                                    data-text="Sesi admin kamu akan berakhir dan harus login lagi."
                                    data-submit="Ya, Logout">Logout</button>
                            </form>
                        </li>
                    @elseif(auth()->check())
                        <li class="nav-item"><span class="nav-link">{{ auth()->user()->username }}</span></li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link"
                                    data-confirm
                                    data-title="Logout dari SCREENED?"
                                    data-text="Kamu akan keluar dan harus login lagi untuk menyimpan progres."
                                    data-submit="Ya, Logout">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="nav-link">Login</a>
                        </li>
                    @endif
                </ul>
            </div>
        </nav>

        @if($hasSidebar)
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="{{ route('collection') }}" class="brand-link">
                    <span class="brand-text fw-light">SCREENED</span>
                </a>
            </div>

            <div class="sidebar-wrapper">
                <nav class="mt-2" aria-label="Main navigation">
                    <ul class="nav sidebar-menu flex-column">
                        <li class="nav-item">
                            <a href="{{ route('collection') }}" class="nav-link">
                                <i class="nav-icon bi bi-film"></i>
                                <p>Collection</p>
                            </a>
                        </li>

                        @if(session()->has('admin_id'))
                            <li class="nav-header">ADMIN</li>
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link">
                                    <i class="nav-icon bi bi-speedometer2"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.manage') }}" class="nav-link">
                                    <i class="nav-icon bi bi-table"></i>
                                    <p>Manage</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.add') }}" class="nav-link">
                                    <i class="nav-icon bi bi-plus-circle"></i>
                                    <p>Add New</p>
                                </a>
                            </li>
                        @else
                            @auth
                                <li class="nav-item">
                                    <a href="{{ route('favorites') }}" class="nav-link">
                                        <i class="nav-icon bi bi-star"></i>
                                        <p>Favorites</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('statistics') }}" class="nav-link">
                                        <i class="nav-icon bi bi-bar-chart"></i>
                                        <p>Statistics</p>
                                    </a>
                                </li>
                            @endauth
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>
        @endif

        <main class="app-main">
            <div class="app-content">
                <div class="container-fluid">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')

                </div>
            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
    <script src="{{ asset('AdminLTE-master/dist/js/adminlte.min.js') }}"></script>

    @include('partials.confirm-modal')

    @stack('scripts')
</body>
</html>