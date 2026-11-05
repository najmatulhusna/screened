@extends('layouts.public')

@section('title', 'My Album - SCREENED')

@section('content')
<div class="container my-album-page">

    {{-- ============ A. PAGE HEADING ============ --}}
    <div class="page-head album-head">
        <h1>My Album</h1>
        <p class="album-subtitle">Your personal watch collection</p>
    </div>

    @guest
        {{-- ============ A1. GUEST: belum login ============ --}}
        <div class="card album-guest">
            <div class="card-body text-center p-4 p-md-5">
                <span class="album-guest-icon"><i class="bi bi-collection-play-fill" aria-hidden="true"></i></span>

                <h2 class="h4 mt-3 mb-2">Make your personal album!</h2>

                <p class="text-body-secondary mx-auto mb-4 album-guest-text">
                    Login dan berlangganan SCREENED VIP untuk menyimpan tontonan pribadi,
                    mengatur status, rating, catatan, dan melihat perkembangannya.
                </p>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-gold">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Login
                    </a>
                    <a href="{{ route('vip') }}" class="btn btn-outline-light">
                        <i class="bi bi-stars" aria-hidden="true"></i> Explore VIP
                    </a>
                </div>
            </div>
        </div>

    @else
        {{-- ============ B. BANNER FREE / VIP EXPIRED ============ --}}
        @if(! $isVip)
            <div class="card album-upsell">
                <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 p-3 p-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="album-upsell-icon"><i class="bi bi-lock-fill" aria-hidden="true"></i></span>
                        <div>
                            <p class="mb-1 fw-semibold">
                                Unlock your personal watch collection with SCREENED VIP.
                            </p>
                            <p class="mb-0 small text-body-secondary">
                                @if($vipExpired)
                                    VIP kamu sudah berakhir — seluruh judul tetap tersimpan dan bisa dilihat.
                                @else
                                    Tambah judul dari Collection atau secara manual, lalu atur status, rating, dan catatanmu.
                                @endif
                            </p>
                        </div>
                    </div>

                    <button type="button" class="btn btn-gold flex-shrink-0"
                        data-bs-toggle="modal" data-bs-target="#vipAlbumModal">
                        <i class="bi bi-stars" aria-hidden="true"></i>
                        {{ $vipExpired ? 'Renew VIP' : 'Upgrade to VIP' }}
                    </button>
                </div>
            </div>
        @endif

        {{-- ============ D. ACTION BUTTONS (VIP) ============ --}}
        @if($isVip)
            <div class="album-actions d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#collectionPickerModal">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i> Add from Collection
                </button>
                <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#albumFormModal">
                    <i class="bi bi-pencil-square" aria-hidden="true"></i> Add Manual
                </button>
            </div>
        @endif

        {{-- ============ E. FILTER ============ --}}
        @if($summary['total'] > 0)
            <div class="card album-filters">
                <div class="card-body p-2">
                    <form action="{{ route('myalbum.index') }}" method="GET">
                        <div class="row g-2 align-items-end">

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small mb-1" for="albumSearch">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="search" name="search" id="albumSearch" class="form-control"
                                        placeholder="Cari judul..." value="{{ request('search') }}">
                                </div>
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label small mb-1" for="albumType">Type</label>
                                <select name="type" id="albumType" class="form-select">
                                    <option value="">All types</option>
                                    @foreach(\App\Models\UserWatchlist::TYPES as $typeOption)
                                        <option value="{{ $typeOption }}" @selected(request('type') === $typeOption)>
                                            {{ $typeOption === 'Tv Show' ? 'TV Show' : $typeOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label small mb-1" for="albumGenre">Genre</label>
                                <select name="genre" id="albumGenre" class="form-select">
                                    <option value="">All Genres</option>
                                    @foreach($genres as $genreOption)
                                        <option value="{{ $genreOption }}" @selected(request('genre') === $genreOption)>
                                            {{ $genreOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label small mb-1" for="albumStatus">Watch Status</label>
                                <select name="status" id="albumStatus" class="form-select">
                                    <option value="">All statuses</option>
                                    @foreach(\App\Models\UserWatchlist::STATUSES as $statusOption)
                                        <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>
                                            {{ $statusOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-4 col-lg-1">
                                <label class="form-label small mb-1" for="albumSort">Sort</label>
                                <select name="sort" id="albumSort" class="form-select">
                                    <option value="recent" @selected(request('sort') === 'recent')>Terbaru</option>
                                    <option value="oldest" @selected(request('sort') === 'oldest')>Terlama</option>
                                    <option value="title" @selected(request('sort') === 'title')>Title A-Z</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-2 col-lg-2 d-grid d-md-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="bi bi-search"></i> Apply
                                </button>
                                <a href="{{ route('myalbum.index') }}" class="btn btn-outline-secondary flex-fill">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ============ F. ALBUM GRID ============ --}}
        @if($summary['total'] === 0)
            <div class="card album-empty">
                <div class="card-body text-center p-4 p-md-5">
                    <i class="bi bi-collection-play album-empty-icon" aria-hidden="true"></i>
                    <p class="fw-semibold mb-1">Albummu masih kosong.</p>
                    <p class="text-body-secondary mb-3 small">
                        @if($isVip)
                            Tambahkan judul dari Collection atau tulis manual untuk mulai mencatat.
                        @else
                            Upgrade ke VIP untuk mulai mengisi My Album.
                        @endif
                    </p>

                    @if($isVip)
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                            <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#collectionPickerModal">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i> Add from Collection
                            </button>
                            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#albumFormModal">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i> Add Manual
                            </button>
                        </div>
                    @else
                        <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#vipAlbumModal">
                            <i class="bi bi-stars" aria-hidden="true"></i> Upgrade to VIP
                        </button>
                    @endif
                </div>
            </div>
        @else
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3 album-grid">
                @forelse($items as $entry)
                    <div class="col">
                        <a href="{{ route('myalbum.show', $entry->id) }}"
                            class="card album-card h-100 text-decoration-none text-reset">
                            <div class="position-relative">
                                <div class="ratio ratio-16x9">
                                    @if($entry->poster)
                                        <img src="{{ asset('storage/' . $entry->poster) }}"
                                            class="card-img-top object-fit-cover" alt="{{ $entry->title }}">
                                    @else
                                        <div class="card-img-top d-flex align-items-center justify-content-center bg-body-secondary text-body-secondary">
                                            <i class="bi bi-film fs-1"></i>
                                        </div>
                                    @endif
                                </div>

                                <span class="badge bg-dark position-absolute top-0 start-0 m-1">
                                    {{ $entry->type === 'Tv Show' ? 'TV Show' : $entry->type }}
                                </span>

                                @if($entry->watch_item_id === null)
                                    <span class="badge album-manual-badge position-absolute top-0 end-0 m-1">
                                        <i class="bi bi-pencil" aria-hidden="true"></i> Manual
                                    </span>
                                @endif
                            </div>

                            <div class="card-body p-2 album-card-body">
                                <div class="fw-semibold small text-truncate" title="{{ $entry->title }}">
                                    {{ $entry->title }}
                                </div>

                                <div class="small text-body-secondary text-truncate">
                                    {{ $entry->release_year ?? '—' }}
                                    @if($entry->genre)
                                        &middot; {{ $entry->genre }}
                                    @endif
                                </div>

                                <div class="album-card-foot">
                                    <span class="album-status status-{{ str()->slug($entry->watch_status, '-') }}">
                                        {{ $entry->watch_status }}
                                    </span>

                                    @if($entry->personal_rating !== null)
                                        <span class="album-rating">
                                            <i class="bi bi-star-fill" aria-hidden="true"></i>
                                            {{ rtrim(rtrim(number_format($entry->personal_rating, 1, '.', ''), '0'), '.') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card album-empty">
                            <div class="card-body text-center text-body-secondary py-5">
                                <i class="bi bi-search fs-1 d-block mb-2" aria-hidden="true"></i>
                                Tidak ada judul yang cocok dengan filter.
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ============ MODAL: ADD FROM COLLECTION ============ --}}
        @if($isVip)
            <div class="modal fade" id="collectionPickerModal" tabindex="-1"
                aria-labelledby="collectionPickerLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h2 class="modal-title h5 mb-0" id="collectionPickerLabel">
                                <i class="bi bi-collection" aria-hidden="true"></i> Add from Collection
                            </h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>

                        <div class="modal-body">
                            <form action="{{ route('myalbum.index') }}" method="GET" class="mb-3">
                                <input type="hidden" name="picker" value="1">
                                @foreach(request()->only(['search', 'type', 'genre', 'status', 'sort']) as $filterKey => $filterValue)
                                    @if($filterValue !== null && $filterValue !== '')
                                        <input type="hidden" name="{{ $filterKey }}" value="{{ $filterValue }}">
                                    @endif
                                @endforeach

                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="search" name="picker_search" class="form-control"
                                        placeholder="Cari judul di Collection..."
                                        value="{{ request('picker_search') }}">
                                    <button class="btn btn-gold" type="submit">Cari</button>
                                </div>
                            </form>

                            @if($pickerItems->isEmpty())
                                <p class="text-body-secondary text-center mb-0 py-4">
                                    {{ request('picker_search') ? 'Judul tidak ditemukan di Collection.' : 'Semua judul Collection sudah ada di albummu.' }}
                                </p>
                            @else
                                <div class="album-picker-list">
                                    @foreach($pickerItems as $item)
                                        <div class="album-picker-row">
                                            <div class="album-picker-poster">
                                                @if($item->poster)
                                                    <img src="{{ asset('storage/' . $item->poster) }}" alt="{{ $item->title }}">
                                                @else
                                                    <i class="bi bi-film" aria-hidden="true"></i>
                                                @endif
                                            </div>

                                            <div class="album-picker-info">
                                                <span class="fw-semibold small d-block text-truncate" title="{{ $item->title }}">
                                                    {{ $item->title }}
                                                </span>
                                                <span class="small text-body-secondary text-truncate d-block">
                                                    {{ $item->type === 'Tv Show' ? 'TV Show' : $item->type }}
                                                    &middot; {{ $item->release_year ?? '—' }}
                                                    @if($item->genre)
                                                        &middot; {{ $item->genre }}
                                                    @endif
                                                </span>
                                            </div>

                                            <form method="POST" action="{{ route('myalbum.store-from-collection', $item->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-gold">
                                                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Add
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                                Tutup
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ============ MODAL: ADD MANUAL ============ --}}
            <div class="modal fade" id="albumFormModal" tabindex="-1"
                aria-labelledby="albumFormLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h2 class="modal-title h5 mb-0" id="albumFormLabel">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i> Add Manual
                            </h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>

                        <form action="{{ route('myalbum.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="modal-body">
                                <p class="text-body-secondary small mb-3">
                                    Catat film atau drama yang belum ada di Collection.
                                </p>
                                @include('partials.album-form-fields', ['entry' => null])
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-gold">
                                    <i class="bi bi-check-lg" aria-hidden="true"></i> Tambah ke My Album
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Batal
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        @endif

        @include('partials.vip-album-modal')

    @endguest
</div>
@endsection

@push('scripts')
<script>
(function () {
    @if(request('picker'))
        var pickerEl = document.getElementById('collectionPickerModal');
        if (pickerEl && window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(pickerEl).show();
        }
    @endif

    @if($errors->any())
        // Validasi Add Manual gagal: buka lagi modalnya (input lama tetap ada).
        var albumFormEl = document.getElementById('albumFormModal');
        if (albumFormEl && window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(albumFormEl).show();
        }
    @endif
})();
</script>
@endpush
