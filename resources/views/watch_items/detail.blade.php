@extends('layouts.public')

@section('title', $watchItem->title . ' - SCREENED')

@section('content')
<div class="container-fluid">

    {{-- Header Action --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <a href="{{ route('collection') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        @auth
            <form method="POST" action="{{ route('watchitem.favorite', $watchItem->id) }}">
                @csrf
                <button type="submit" class="btn btn-sm {{ $isFavorited ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="bi bi-star{{ $isFavorited ? '-fill' : '' }}"></i>
                    {{ $isFavorited ? '★ Favorited' : 'Add to Favorites' }}
                </button>
            </form>
        @endauth
    </div>

    {{-- Detail Card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row justify-content-center mb-3">
                <div class="col-6 col-sm-5 col-md-4 col-lg-3">
                    <div class="card shadow-sm overflow-hidden">
                        @if($watchItem->poster)
                            <div class="ratio ratio-poster">
                                <img src="{{ asset('storage/' . $watchItem->poster) }}"
                                    class="object-fit-cover" alt="{{ $watchItem->title }}">
                            </div>
                        @else
                            <div class="ratio ratio-poster d-flex align-items-center justify-content-center bg-body-secondary text-body-secondary">
                                <i class="bi bi-film fs-1"></i>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="text-center mb-3">
                <h1 class="h3 mb-2">{{ $watchItem->title }}</h1>
                <p class="text-body-secondary mb-2">
                    {{ $watchItem->type }} &middot; {{ $watchItem->release_year }} &middot; {{ $watchItem->genre }}
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                    <span class="badge text-bg-dark">{{ $watchItem->type }}</span>

                    @if($watchItem->rating)
                        <span class="badge text-bg-warning">
                            <i class="bi bi-star-fill"></i> {{ $watchItem->rating }}/10
                        </span>
                    @endif

                    @if($watchItem->total_episodes)
                        <span class="badge text-bg-secondary">
                            <i class="bi bi-collection-play"></i> {{ $watchItem->total_episodes }} Episode
                        </span>
                    @endif

                    @if($watchItem->duration)
                        <span class="badge text-bg-secondary">
                            <i class="bi bi-clock"></i> {{ $watchItem->duration }} min
                        </span>
                    @endif
                </div>
            </div>

            @if($watchItem->synopsis)
                <div class="mb-3">
                    <h2 class="h6 fw-semibold text-body-secondary mb-2">Synopsis</h2>
                    <p class="detail-synopsis mb-0 lh-lg">{{ $watchItem->synopsis }}</p>
                </div>
            @endif

            <div class="row g-3 detail-meta-list">
                @if($watchItem->cast_list)
                    <div class="col-12">
                        <div class="detail-meta">
                            <span class="fw-semibold text-body-secondary">Cast</span>
                            <span class="detail-meta-value">{{ $watchItem->cast_list }}</span>
                        </div>
                    </div>
                @endif

                @if($watchItem->director_creator)
                    <div class="col-12">
                        <div class="detail-meta">
                            <span class="fw-semibold text-body-secondary">Director</span>
                            <span class="detail-meta-value">{{ $watchItem->director_creator }}</span>
                        </div>
                    </div>
                @endif

                @if($watchItem->country)
                    <div class="col-12">
                        <div class="detail-meta">
                            <span class="fw-semibold text-body-secondary">Country</span>
                            <span class="detail-meta-value">{{ $watchItem->country }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- SECTION FORM TERPISAH (PROGRESS & REVIEW) --}}
    @if($canTrack)
    <div class="row g-3 mb-4">
        
        {{-- FORM 1: QUICK PROGRESS --}}
        <div class="col-12 col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 bg-body-tertiary">
                    <h3 class="card-title h6 mb-0">Progress</h3>
                </div>
                <div class="card-body p-3">
                    <form method="POST" action="{{ route('watchitem.review', $watchItem->id) }}">
                        @csrf
                        
                        {{-- Hidden inputs agar data review tidak hilang saat update progress --}}
                        <input type="hidden" name="rating" value="{{ $userReview?->rating }}">
                        <input type="hidden" name="review_text" value="{{ $userReview?->review_text }}">

                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1" for="progress_status">Status</label>
                            <select name="status" id="progress_status" class="form-select form-select-sm" required>
                                <option value="Plan to Watch" {{ old('status', $userReview?->status) === 'Plan to Watch' ? 'selected' : '' }}>Plan to Watch</option>
                                <option value="Watching" {{ old('status', $userReview?->status) === 'Watching' ? 'selected' : '' }}>Watching</option>
                                <option value="Completed" {{ old('status', $userReview?->status) === 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-1" for="progress_episode">Episode Watched</label>
                            <input type="number" name="episode_watched" id="progress_episode" class="form-control form-control-sm" min="0"
                                value="{{ old('episode_watched', $userReview?->episode_watched ?? 0) }}">
                        </div>

                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-check-lg"></i> Update Progress
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- FORM 2: WRITE REVIEW & RATING --}}
        <div class="col-12 col-md-7">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 bg-body-tertiary">
                    <h3 class="card-title h6 mb-0">Review & Rating</h3>
                </div>
                <div class="card-body p-3">
                    <form method="POST" action="{{ route('watchitem.review', $watchItem->id) }}">
                        @csrf

                        {{-- Hidden inputs agar status & episode tidak ter-reset saat simpan review --}}
                        <input type="hidden" name="status" value="{{ $userReview?->status ?? 'Completed' }}">
                        <input type="hidden" name="episode_watched" value="{{ $userReview?->episode_watched ?? 0 }}">

                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1" for="review_rating">Rating (1-10)</label>
                            <input type="number" name="rating" id="review_rating" class="form-control form-control-sm"
                                min="1" max="10" step="0.1"
                                value="{{ old('rating', $userReview?->rating) }}" placeholder="Contoh: 8.5 (Opsional)">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-1" for="review_text">Review</label>
                            <textarea name="review_text" id="review_text" class="form-control form-control-sm" rows="3" placeholder="Tulis ulasan Anda...">{{ old('review_text', $userReview?->review_text) }}</textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="bi bi-send"></i> Submit Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    @else
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center text-body-secondary">
                <i class="bi bi-person-circle fs-3 d-block mb-2"></i>
                <a href="{{ route('login') }}">Login</a> untuk memperbarui status atau menulis review.
            </div>
        </div>
    @endif

    {{-- Reviews List Section --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h3 class="card-title h6 mb-0"><i class="bi bi-chat-left-text"></i> Reviews ({{ $watchItem->reviews->count() }})</h3>
        </div>

        <div class="card-body">
            {{-- Admin Reviews --}}
            @foreach($adminReviews as $review)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">
                                <i class="bi bi-shield-lock text-body-secondary"></i>
                                {{ $review->admin ? $review->admin->username : 'Admin' }}
                            </span>
                            <span class="d-flex flex-wrap align-items-center gap-2">
                                @if($review->rating)
                                    <span class="small text-body-secondary">Rating</span>
                                    <span class="badge text-bg-warning">
                                        <i class="bi bi-star-fill"></i> {{ $review->rating }}/10
                                    </span>
                                @endif
                                <span class="badge text-bg-secondary">{{ $review->status }}</span>
                                <span class="badge text-bg-light border">Ep: {{ $review->episode_watched }}</span>
                            </span>
                        </div>
                        <p class="mb-0">{{ $review->review_text }}</p>

                        @if(session()->has('admin_id'))
                            <form method="POST" action="{{ route('admin.review.destroy', $review->id) }}" class="text-end mt-2 mb-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    data-confirm
                                    data-title="Hapus Review & Rating?"
                                    data-text="Review untuk {{ $watchItem->title }} akan dihapus permanen beserta ratingnya."
                                    data-submit="Ya, Hapus">
                                    <i class="bi bi-trash"></i> Hapus Review
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- User Reviews (First 3) --}}
            @php $visibleUserReviews = $userReviews->take(3); @endphp

            @foreach($visibleUserReviews as $review)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">
                                <i class="bi bi-person-circle text-body-secondary"></i>
                                {{ $review->user ? $review->user->username : 'User' }}
                            </span>
                            <span class="d-flex flex-wrap align-items-center gap-2">
                                @if($review->rating)
                                    <span class="small text-body-secondary">Rating</span>
                                    <span class="badge text-bg-warning">
                                        <i class="bi bi-star-fill"></i> {{ $review->rating }}/10
                                    </span>
                                @endif
                                <span class="badge text-bg-secondary">{{ $review->status }}</span>
                                <span class="badge text-bg-light border">Ep: {{ $review->episode_watched }}</span>
                            </span>
                        </div>
                        <p class="mb-0">{{ $review->review_text }}</p>

                        @if(session()->has('admin_id'))
                            <form method="POST" action="{{ route('admin.review.destroy', $review->id) }}" class="text-end mt-2 mb-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    data-confirm
                                    data-title="Hapus Review & Rating?"
                                    data-text="Review oleh {{ $review->user?->username ?? 'user' }} untuk {{ $watchItem->title }} akan dihapus permanen beserta ratingnya."
                                    data-submit="Ya, Hapus">
                                    <i class="bi bi-trash"></i> Hapus Review
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Hidden User Reviews (>3) --}}
            @if($userReviews->count() > 3)
                <div id="more-reviews" style="display:none">
                    @foreach($userReviews->skip(3) as $review)
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold">
                                        <i class="bi bi-person-circle text-body-secondary"></i>
                                        {{ $review->user ? $review->user->username : 'User' }}
                                    </span>
                                    <span class="d-flex flex-wrap align-items-center gap-2">
                                        @if($review->rating)
                                            <span class="small text-body-secondary">Rating</span>
                                            <span class="badge text-bg-warning">
                                                <i class="bi bi-star-fill"></i> {{ $review->rating }}/10
                                            </span>
                                        @endif
                                        <span class="badge text-bg-secondary">{{ $review->status }}</span>
                                        <span class="badge text-bg-light border">Ep: {{ $review->episode_watched }}</span>
                                    </span>
                                </div>
                                <p class="mb-0">{{ $review->review_text }}</p>

                                @if(session()->has('admin_id'))
                                    <form method="POST" action="{{ route('admin.review.destroy', $review->id) }}" class="text-end mt-2 mb-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            data-confirm
                                            data-title="Hapus Review & Rating?"
                                            data-text="Review oleh {{ $review->user?->username ?? 'user' }} untuk {{ $watchItem->title }} akan dihapus permanen beserta ratingnya."
                                            data-submit="Ya, Hapus">
                                            <i class="bi bi-trash"></i> Hapus Review
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <button class="btn btn-outline-primary btn-sm"
                    onclick="document.getElementById('more-reviews').style.display='block'; this.style.display='none'">
                    Lainnya ({{ $userReviews->count() - 3 }} lainnya)
                </button>
            @endif

            @if($watchItem->reviews->isEmpty())
                <p class="text-body-secondary mb-0">Belum ada review.</p>
            @endif
        </div>
    </div>

</div>
@endsection