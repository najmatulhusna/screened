@extends('layouts.public')

@section('title', $watchItem->title . ' - SCREENED')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
        <a href="{{ route('collection') }}" class="text-decoration-none text-body-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        @if($watchItem->trailer_url)
            <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#trailerModal">
                <i class="bi bi-film"></i> Trailer
            </button>
        @endif
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

                <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mb-3 detail-actions">
                    <span class="badge">{{ $watchItem->type }}</span>

                    @if($watchItem->rating)
                        <span class="badge">
                            <i class="bi bi-star-fill"></i> {{ $watchItem->rating }}/10
                        </span>
                    @endif

                    @if($watchItem->total_episodes)
                        <span class="badge">
                            <i class="bi bi-collection-play"></i> {{ $watchItem->total_episodes }} Episode
                        </span>
                    @endif

                    @if($watchItem->duration)
                        <span class="badge">
                            <i class="bi bi-clock"></i> {{ $watchItem->duration }} min
                        </span>
                    @endif

                    <a href="{{ route('watch', ['id' => $watchItem->id]) }}" class="btn btn-gold btn-sm">
                        <i class="bi bi-play-fill"></i>
                        {{ $watchItem->isMovie() ? 'Nonton Film' : 'Nonton Semua' }}
                    </a>

                    @auth
                        <form method="POST" action="{{ route('watchitem.favorite', $watchItem->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $isFavorited ? 'btn-warning' : 'btn-outline-warning' }}">
                                <i class="bi bi-star{{ $isFavorited ? '-fill' : '' }}"></i>
                                {{ $isFavorited ? '★ Favorited' : 'Add to Favorites' }}
                            </button>
                        </form>
                    @endauth
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

                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label small mb-1" for="status">Status</label>
                        <select name="status" id="status" class="form-select form-select-sm" required>
                            <option value="Plan to Watch" {{ old('status', $userReview?->status) === 'Plan to Watch' ? 'selected' : '' }}>Plan to Watch</option>
                            <option value="Watching" {{ old('status', $userReview?->status) === 'Watching' ? 'selected' : '' }}>Watching</option>
                            <option value="Completed" {{ old('status', $userReview?->status) === 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label small mb-1" for="episode_watched">Episode Watched</label>
                        <input type="number" name="episode_watched" id="episode_watched" class="form-control form-control-sm" min="0"
                            value="{{ old('episode_watched', $userReview?->episode_watched ?? 0) }}">
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
                            value="{{ old('rating', $userReview?->rating) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small mb-1" for="review_text">Review</label>
                        <textarea name="review_text" id="review_text" class="form-control form-control-sm" rows="2">{{ old('review_text', $userReview?->review_text) }}</textarea>
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
                                @if($review->user && $review->user->isVip())
                                    <span class="badge vip-badge"><i class="bi bi-crown-fill"></i> VIP</span>
                                @endif
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

{{-- ============ MODAL TRAILER ============ --}}
@if($watchItem->trailer_url)
<div class="modal fade" id="trailerModal" tabindex="-1" aria-labelledby="trailerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content trailer-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-white h6" id="trailerModalLabel">
                    <i class="bi bi-film"></i> Trailer - {{ $watchItem->title }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-0">
                <div class="trailer-stage">
                    <div class="ratio ratio-16x9 trailer-holder" id="trailerHolder"></div>

                    {{-- arahan muncul saat trailer selesai --}}
                    <div id="trailerEndCta" class="trailer-end-cta d-none">
                        <div class="text-center">
                            <p class="mb-3 fw-semibold text-white">Mau nonton versi lengkapnya?</p>
                            <a href="{{ route('watch', ['id' => $watchItem->id]) }}" class="btn btn-gold">
                                <i class="bi bi-play-circle-fill"></i> Nonton Full Version
                            </a>
                            <button type="button" class="btn btn-outline-light btn-sm d-block mx-auto mt-2" id="btnReplayTrailer">
                                <i class="bi bi-arrow-counterclockwise"></i> Putar Ulang Trailer
                            </button>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                    <span class="small text-body-secondary" id="trailerHint">
                        Trailer gratis &middot; setelah selesai, lanjut nonton full.
                    </span>
                    <a href="{{ route('watch', ['id' => $watchItem->id]) }}" class="btn btn-gold btn-sm">
                        <i class="bi bi-play-fill"></i> {{ $watchItem->isMovie() ? 'Nonton Film' : 'Nonton Semua' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
@if($watchItem->trailer_url)
<script>
    (function () {
        var TRAILER_URL = @json($watchItem->trailer_url);
        var WATCH_URL = @json(route('watch', ['id' => $watchItem->id]));

        var holder = document.getElementById('trailerHolder');
        var endCta = document.getElementById('trailerEndCta');
        var ytPending = null;
        var ytLoading = false;

        function isYouTube(url) {
            return /(youtu\.be|youtube\.com)/i.test(url);
        }

        function ytId(url) {
            var m = url.match(/youtu\.be\/([A-Za-z0-9_-]+)/)
                 || url.match(/[?&]v=([A-Za-z0-9_-]+)/)
                 || url.match(/youtube\.com\/shorts\/([A-Za-z0-9_-]+)/)
                 || url.match(/youtube\.com\/embed\/([A-Za-z0-9_-]+)/);
            return m ? m[1] : null;
        }

        function showEndCta() {
            if (endCta) endCta.classList.remove('d-none');
        }

        function loadYouTubeApi() {
            if (ytLoading) return;
            ytLoading = true;
            var s = document.createElement('script');
            s.src = 'https://www.youtube.com/iframe_api';
            document.head.appendChild(s);
        }

        window.onYouTubeIframeAPIReady = function () {
            if (ytPending) {
                var mount = ytPending;
                ytPending = null;
                mount();
            }
        };

        function renderTrailer() {
            if (!holder) return;

            if (endCta) endCta.classList.add('d-none');
            holder.innerHTML = '';

            if (isYouTube(TRAILER_URL)) {
                var id = ytId(TRAILER_URL);
                if (!id) return;

                var mountPoint = document.createElement('div');
                holder.appendChild(mountPoint);

                var mount = function () {
                    new YT.Player(mountPoint, {
                        videoId: id,
                        playerVars: { autoplay: 1, rel: 0 },
                        events: {
                            onStateChange: function (e) {
                                if (e.data === YT.PlayerState.ENDED) showEndCta();
                            }
                        }
                    });
                };

                if (window.YT && window.YT.Player) {
                    mount();
                } else {
                    ytPending = mount;
                    loadYouTubeApi();
                }
            } else if (/\.(mp4|webm|ogg|ogv)(\?|$)/i.test(TRAILER_URL)) {
                var video = document.createElement('video');
                video.src = TRAILER_URL;
                video.controls = true;
                video.autoplay = true;
                video.playsInline = true;
                video.className = 'trailer-video';
                video.addEventListener('ended', showEndCta);
                holder.appendChild(video);
            } else {
                var frame = document.createElement('iframe');
                frame.src = TRAILER_URL;
                frame.allow = 'autoplay; fullscreen; encrypted-media';
                frame.allowFullscreen = true;
                frame.className = 'trailer-iframe';
                holder.appendChild(frame);
            }
        }

        var modalEl = document.getElementById('trailerModal');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', renderTrailer);
            modalEl.addEventListener('hidden.bs.modal', function () {
                if (holder) holder.innerHTML = '';
                ytPending = null;
            });
        }

        var replay = document.getElementById('btnReplayTrailer');
        if (replay) replay.addEventListener('click', renderTrailer);
    })();
</script>
@endif
@endpush