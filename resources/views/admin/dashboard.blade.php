@extends('layouts.adminlte')

@section('title', 'Admin Dashboard - SCREENED')

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Admin Dashboard</h1>
        <a href="{{ route('admin.add') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add New Item
        </a>
    </div>

    <div class="row">
        @foreach($typeStats as $stat)
            <div class="col-6 col-lg-3">
                <div class="small-box text-bg-{{ $stat['color'] }} mb-4">
                    <div class="inner">
                        <h3>{{ $stat['total'] }}</h3>
                        <p>{{ $stat['type'] }}</p>
                    </div>
                    <i class="bi {{ $stat['icon'] }} small-box-icon"></i>
                    <a href="{{ route('admin.manage') }}"
                        class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        {{ $stat['percent'] }}% dari total <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mb-4">
        <div class="col-6 col-lg">
            <div class="info-box shadow-sm">
                <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-collection"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Items</span>
                    <span class="info-box-number">{{ $totalItems }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="info-box shadow-sm">
                <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-chat-left-text"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Reviews</span>
                    <span class="info-box-number">{{ $totalReviews }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="info-box shadow-sm">
                <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-people"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Users</span>
                    <span class="info-box-number">{{ $totalUsers }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="info-box shadow-sm">
                <span class="info-box-icon text-bg-warning shadow-sm"><i class="bi bi-shield-lock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Admins</span>
                    <span class="info-box-number">{{ $totalAdmins }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="info-box shadow-sm">
                <span class="info-box-icon text-bg-danger shadow-sm"><i class="bi bi-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Avg Rating</span>
                    <span class="info-box-number">{{ number_format($avgRating, 1) }}<small>/10</small></span>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <h2 class="h6 fw-semibold mb-0">Semua Item ({{ $totalItems }})</h2>
        <a href="{{ route('admin.manage') }}" class="small text-decoration-none">Kelola data <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
        @forelse($watchItems as $item)
            <div class="col">
                <div class="card h-100 shadow-sm overflow-hidden">
                    <a href="{{ route('watchitem.detail', $item->id) }}" class="text-decoration-none text-reset d-block">
                        <div class="position-relative">
                            <div class="ratio ratio-16x9">
                                @if($item->poster)
                                    <img src="{{ asset('storage/' . $item->poster) }}"
                                        class="card-img-top object-fit-cover" alt="{{ $item->title }}">
                                @else
                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-body-secondary text-body-secondary">
                                        <i class="bi bi-film fs-1"></i>
                                    </div>
                                @endif
                            </div>
                            <span class="badge bg-dark position-absolute top-0 end-0 m-1">{{ $item->type }}</span>
                        </div>

                        <div class="card-body p-2">
                            <div class="fw-semibold small text-truncate" title="{{ $item->title }}">{{ $item->title }}</div>
                            <div class="small text-body-secondary text-truncate">
                                {{ $item->release_year }} &middot; {{ $item->genre }}
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-body-secondary py-5">
                        <i class="bi bi-collection fs-1 d-block mb-2"></i>
                        Belum ada item.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

@endsection
