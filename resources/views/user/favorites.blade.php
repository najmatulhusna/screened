@extends('layouts.public')

@section('title', 'Favorites - SCREENED')

@section('content')
<div class="container">
    <div class="page-head">
        <h1>My Favorites</h1>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
        @forelse($watchItems as $item)
            @php $review = $userReviews->get($item->id); @endphp
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
                            @if($review?->rating)
                                <span class="badge bg-warning text-dark position-absolute bottom-0 end-0 m-1">
                                    <i class="bi bi-star-fill"></i> {{ $review->rating }}
                                </span>
                            @endif
                        </div>

                        <div class="card-body p-2">
                            <div class="fw-semibold small text-truncate" title="{{ $item->title }}">{{ $item->title }}</div>
                            <div class="small text-body-secondary text-truncate">
                                {{ $item->release_year }} &middot; {{ $item->genre }}
                            </div>

                            @if($review)
                                <div class="small text-body-secondary">
                                    {{ $review->status }}
                                    &middot; Ep {{ $review->episode_watched }}/{{ $item->total_episodes ?? '?' }}
                                </div>
                            @endif
                        </div>
                    </a>

                    <div class="fav-actions">
                        <form method="POST" action="{{ route('favorites.destroy', $item->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-light w-100"
                                data-confirm
                                data-title="Hapus dari Favorites?"
                                data-text="{{ $item->title }} akan dikeluarkan dari Favorites. Progress & review tetap tersimpan di Statistics."
                                data-submit="Hapus dari Favorites">
                                <i class="bi bi-heart-break"></i> Hapus dari Favorites
                            </button>
                        </form>

                        @if($review)
                            <form method="POST" action="{{ route('watchitem.progress.destroy', $item->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                                    data-confirm
                                    data-title="Hapus Progress?"
                                    data-text="Progress, rating, dan review milikmu untuk {{ $item->title }} akan dihapus permanen dari Statistics."
                                    data-submit="Ya, Hapus Progress">
                                    <i class="bi bi-trash"></i> Hapus Progress
                                </button>
                            </form>
                        @else
                            <div class="fav-no-progress">
                                <i class="bi bi-info-circle"></i> Belum ada progress
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card empty-state">
                    <div class="card-body text-center text-body-secondary py-5">
                        <i class="bi bi-star fs-1 d-block mb-2"></i>
                        Belum ada favorit.
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@endsection
