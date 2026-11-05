@extends('layouts.public')

@section('title', $entry->title . ' - My Album - SCREENED')

@section('content')
<div class="container my-album-page album-detail-page">

    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
        <a href="{{ route('myalbum.index') }}" class="text-decoration-none text-body-secondary">
            <i class="bi bi-arrow-left"></i> My Album
        </a>

        @if($entry->fromCollection() && $entry->watchItem)
            <a href="{{ route('watchitem.detail', $entry->watch_item_id) }}"
                class="text-decoration-none text-body-secondary">
                <i class="bi bi-collection"></i> Lihat di Collection
            </a>
        @endif
    </div>

    <div class="page-head album-head">
        <h1>{{ $entry->title }}</h1>
        <p class="album-subtitle">
            {{ $entry->type === 'Tv Show' ? 'TV Show' : $entry->type }}
            @if($entry->release_year)
                &middot; {{ $entry->release_year }}
            @endif
            @if($entry->genre)
                &middot; {{ $entry->genre }}
            @endif
        </p>
    </div>

    <div class="row g-4">
        {{-- ============ POSTER ============ --}}
        <div class="col-12 col-md-4 col-lg-3">
            <div class="card album-detail-poster overflow-hidden">
                @if($entry->poster)
                    <div class="ratio ratio-2x3">
                        <img src="{{ asset('storage/' . $entry->poster) }}"
                            class="object-fit-cover" alt="{{ $entry->title }}">
                    </div>
                @else
                    <div class="ratio ratio-2x3 d-flex align-items-center justify-content-center bg-body-secondary text-body-secondary">
                        <i class="bi bi-film" style="font-size:3rem" aria-hidden="true"></i>
                    </div>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                @if($isVip)
                    <button type="button" class="btn btn-gold btn-sm flex-fill"
                        data-bs-toggle="modal" data-bs-target="#albumEditModal">
                        <i class="bi bi-pencil" aria-hidden="true"></i> Edit
                    </button>

                    <form method="POST" action="{{ route('myalbum.destroy', $entry->id) }}" class="flex-fill">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                            data-confirm
                            data-title="Hapus dari My Album?"
                            data-text="{{ $entry->title }} akan dihapus dari album pribadimu. Data di Collection tidak ikut terhapus."
                            data-submit="Ya, Hapus">
                            <i class="bi bi-trash" aria-hidden="true"></i> Remove
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-gold btn-sm flex-fill"
                        data-bs-toggle="modal" data-bs-target="#vipAlbumModal">
                        <i class="bi bi-pencil" aria-hidden="true"></i> Edit
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm flex-fill"
                        data-bs-toggle="modal" data-bs-target="#vipAlbumModal">
                        <i class="bi bi-trash" aria-hidden="true"></i> Remove
                    </button>
                @endif
            </div>
        </div>

        {{-- ============ INFORMASI ============ --}}
        <div class="col-12 col-md-8 col-lg-9">

            {{-- Status & data personal --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header py-2">
                    <h2 class="card-title h6 mb-0"><i class="bi bi-person-lines-fill"></i> My Watch Data</h2>
                </div>

                <div class="card-body">
                    <div class="album-personal-grid">
                        <div class="album-personal-item">
                            <span class="album-personal-label">Watch Status</span>
                            <span class="album-status status-{{ str()->slug($entry->watch_status, '-') }}">
                                {{ $entry->watch_status }}
                            </span>
                        </div>

                        <div class="album-personal-item">
                            <span class="album-personal-label">Personal Rating</span>
                            @if($entry->personal_rating !== null)
                                <span class="album-rating album-rating-lg">
                                    <i class="bi bi-star-fill" aria-hidden="true"></i>
                                    {{ rtrim(rtrim(number_format($entry->personal_rating, 1, '.', ''), '0'), '.') }}/10
                                </span>
                            @else
                                <span class="text-body-secondary">—</span>
                            @endif
                        </div>

                        <div class="album-personal-item">
                            <span class="album-personal-label">Date Started</span>
                            <span>{{ $entry->date_started?->format('d M Y') ?? '—' }}</span>
                        </div>

                        <div class="album-personal-item">
                            <span class="album-personal-label">Date Completed</span>
                            <span>{{ $entry->date_completed?->format('d M Y') ?? '—' }}</span>
                        </div>

                        <div class="album-personal-item">
                            <span class="album-personal-label">Source</span>
                            <span>
                                @if($entry->fromCollection())
                                    <i class="bi bi-collection" aria-hidden="true"></i> From Collection
                                @else
                                    <i class="bi bi-pencil" aria-hidden="true"></i> Manual
                                @endif
                            </span>
                        </div>

                        <div class="album-personal-item">
                            <span class="album-personal-label">Added</span>
                            <span>{{ $entry->created_at?->format('d M Y') ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="album-notes mt-3">
                        <span class="album-personal-label">Personal Notes</span>
                        @if($entry->notes)
                            <p class="mb-0 mt-1">{{ $entry->notes }}</p>
                        @else
                            <p class="mb-0 mt-1 text-body-secondary">Belum ada catatan.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Informasi film / drama --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header py-2">
                    <h2 class="card-title h6 mb-0"><i class="bi bi-info-circle"></i> Informasi</h2>
                </div>

                <div class="card-body">
                    @if($entry->synopsis)
                        <p class="detail-synopsis lh-lg">{{ $entry->synopsis }}</p>
                        <hr>
                    @endif

                    <div class="row g-3 detail-meta-list">
                        @if($entry->cast_list)
                            <div class="col-12">
                                <div class="detail-meta">
                                    <span class="fw-semibold text-body-secondary">Cast</span>
                                    <span class="detail-meta-value">{{ $entry->cast_list }}</span>
                                </div>
                            </div>
                        @endif

                        @if($entry->director_creator)
                            <div class="col-12">
                                <div class="detail-meta">
                                    <span class="fw-semibold text-body-secondary">Director / Creator</span>
                                    <span class="detail-meta-value">{{ $entry->director_creator }}</span>
                                </div>
                            </div>
                        @endif

                        @if($entry->country)
                            <div class="col-12">
                                <div class="detail-meta">
                                    <span class="fw-semibold text-body-secondary">Country</span>
                                    <span class="detail-meta-value">{{ $entry->country }}</span>
                                </div>
                            </div>
                        @endif

                        @if($entry->duration)
                            <div class="col-6 col-md-4">
                                <div class="detail-meta">
                                    <span class="fw-semibold text-body-secondary">Duration</span>
                                    <span class="detail-meta-value">{{ $entry->duration }} min</span>
                                </div>
                            </div>
                        @endif

                        @if($entry->total_episodes)
                            <div class="col-6 col-md-4">
                                <div class="detail-meta">
                                    <span class="fw-semibold text-body-secondary">Episodes</span>
                                    <span class="detail-meta-value">{{ $entry->total_episodes }}</span>
                                </div>
                            </div>
                        @endif

                        @if($entry->release_year)
                            <div class="col-6 col-md-4">
                                <div class="detail-meta">
                                    <span class="fw-semibold text-body-secondary">Release Year</span>
                                    <span class="detail-meta-value">{{ $entry->release_year }}</span>
                                </div>
                            </div>
                        @endif

                        @if(! $entry->synopsis && ! $entry->cast_list && ! $entry->director_creator
                            && ! $entry->country && ! $entry->duration && ! $entry->total_episodes
                            && ! $entry->release_year)
                            <div class="col-12">
                                <p class="text-body-secondary mb-0">Tidak ada informasi tambahan.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ============ MODAL: EDIT ============ --}}
    @if($isVip)
        <div class="modal fade" id="albumEditModal" tabindex="-1"
            aria-labelledby="albumEditLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h2 class="modal-title h5 mb-0" id="albumEditLabel">
                            <i class="bi bi-pencil" aria-hidden="true"></i> Edit — {{ $entry->title }}
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <form action="{{ route('myalbum.update', $entry->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="modal-body">
                            @include('partials.album-form-fields', ['entry' => $entry])
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-gold">
                                <i class="bi bi-check-lg" aria-hidden="true"></i> Simpan Perubahan
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
</div>

@include('partials.vip-album-modal')
@endsection

@push('scripts')
<script>
(function () {
    @if($errors->any())
        // Validasi Edit gagal: buka lagi modalnya (input lama tetap ada).
        var editEl = document.getElementById('albumEditModal');
        if (editEl && window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(editEl).show();
        }
    @endif
})();
</script>
@endpush
