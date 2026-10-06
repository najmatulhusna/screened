@extends('layouts.public')

@section('title', $watchItem->title . ' - SCREENED')

@section('content')
<div class="container-fluid">

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

    @if($canTrack)
    <div class="card shadow-sm mb-4">
        <div class="card-header py-2">
            <h3 class="card-title h6 mb-0"><i class="bi bi-list-check"></i> Your Progress</h3>
        </div>

        <div class="card-body p-2 p-md-3">
            <form method="POST" action="{{ route('watchitem.review', $watchItem->id) }}">
                @csrf

                {{-- field milik form Review dikirim ulang supaya tidak tertimpa kosong --}}
                <input type="hidden" name="rating" value="{{ $userReview?->rating ?? 1 }}">
                <input type="hidden" name="review_text" value="{{ $userReview?->review_text ?? '' }}">

                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label small mb-1" for="status">Status</label>
                        <select name="status" id="status" class="form-select form-select-sm" required>
                            <option value="Plan to Watch" {{ $userReview && $userReview->status === 'Plan to Watch' ? 'selected' : '' }}>Plan to Watch</option>
                            <option value="Watching" {{ $userReview && $userReview->status === 'Watching' ? 'selected' : '' }}>Watching</option>
                            <option value="Completed" {{ $userReview && $userReview->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label small mb-1" for="episode_watched">Episode Watched</label>
                        <input type="number" name="episode_watched" id="episode_watched" class="form-control form-control-sm" min="0"
                            value="{{ $userReview ? $userReview->episode_watched : old('episode_watched', 0) }}">
                    </div>

                    <div class="col-6 col-md-4">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-check-lg"></i> Save Progress
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header py-2">
            <h3 class="card-title h6 mb-0"><i class="bi bi-star"></i> {{ $userReview ? 'Update Your Review' : 'Add Your Review' }}</h3>
        </div>

        <div class="card-body p-2 p-md-3">
            <form method="POST" action="{{ route('watchitem.review', $watchItem->id) }}">
                @csrf

                {{-- field milik form Progress dikirim ulang supaya tidak tertimpa --}}
                <input type="hidden" name="status" value="{{ $userReview?->status ?? 'Plan to Watch' }}">
                <input type="hidden" name="episode_watched" value="{{ $userReview?->episode_watched ?? 0 }}">

                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-3">
                        <label class="form-label small mb-1" for="rating">Rating (1-10)</label>
                        <input type="number" name="rating" id="rating" class="form-control form-control-sm"
                            min="1" max="10" step="0.1" required
                            value="{{ $userReview ? $userReview->rating : old('rating') }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small mb-1" for="review_text">Review</label>
                        <textarea name="review_text" id="review_text" class="form-control form-control-sm" rows="2">{{ $userReview ? $userReview->review_text : old('review_text') }}</textarea>
                    </div>

                    <div class="col-6 col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-send"></i> {{ $userReview ? 'Update Review' : 'Submit Review' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @else
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center text-body-secondary">
                <i class="bi bi-person-circle fs-3 d-block mb-2"></i>
                <a href="{{ route('login') }}">Login</a> untuk menulis review.
            </div>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-chat-left-text"></i> Reviews ({{ $watchItem->reviews->count() }})</h3>
        </div>

        <div class="card-body">

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
