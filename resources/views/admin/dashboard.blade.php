@extends('layouts.adminlte')

@section('title', 'Admin Dashboard - SCREENED')

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Admin Dashboard</h1>
        <a href="{{ route('admin.manage') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add New Item
        </a>
    </div>

    {{-- ===== RINGKASAN ANGKA ===== --}}
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

    {{-- ===== 2 GRID BERDAMPINGAN: kiri = konten per tipe, kanan = transaksi ===== --}}
    <div class="row g-3 mb-4 align-items-stretch">

        {{-- GRID 1 (kiri): Movie / Drama / Series / Tv Show --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h3 class="card-title h6 mb-0"><i class="bi bi-film"></i> Konten</h3>
                    <a href="{{ route('admin.manage') }}" class="small text-decoration-none">
                        Kelola item <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($typeStats as $stat)
                            <div class="col-6">
                                <div class="border rounded p-3 h-100 d-flex align-items-center justify-content-between gap-2">
                                    <div>
                                        <div class="small text-body-secondary">{{ $stat['type'] }}</div>
                                        <div class="h4 fw-bold mb-0">{{ $stat['total'] }}</div>
                                    </div>
                                    <span class="badge text-bg-{{ $stat['color'] }}">{{ $stat['percent'] }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- GRID 2 (kanan): Transaksi --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h3 class="card-title h6 mb-0"><i class="bi bi-credit-card"></i> Transaksi VIP</h3>
                    <a href="{{ route('admin.transactions') }}" class="small text-decoration-none">
                        Semua transaksi <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-body-secondary">Pendapatan VIP</div>
                                <div class="h4 fw-bold mb-0 text-success">Rp {{ number_format($txStats['success_amount'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-body-secondary">Total Transaksi</div>
                                <div class="h4 fw-bold mb-0">{{ $txStats['total_count'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-body-secondary">Berhasil</div>
                                <div class="h4 fw-bold mb-0 text-success">{{ $txStats['success_count'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-body-secondary">Pending</div>
                                <div class="h4 fw-bold mb-0 text-warning">{{ $txStats['pending_count'] }}</div>
                            </div>
                        </div>
                    </div>
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
