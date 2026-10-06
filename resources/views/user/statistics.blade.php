@extends('layouts.public')

@section('title', 'Statistics - SCREENED')

@section('content')
<div class="container">
    <div class="page-head">
        <h1>My Statistics</h1>
    </div>

    <div class="stats-summary">
        <p>Total Completed: {{ $totalWatched }}</p>
        <p>Watching: {{ $totalWatching }}</p>
        <p>Plan to Watch: {{ $totalPlan }}</p>
        <p>Total Reviews: {{ $reviews->count() }}</p>
    </div>

    <h2>My History</h2>
    @forelse($reviews as $review)
        <div class="review-card">
            <a href="{{ route('watchitem.detail', $review->watchItem->id) }}">
                <strong>{{ $review->watchItem->title }}</strong>
            </a>
            <span>{{ $review->rating }}/10</span>
            <span>{{ $review->status }}</span>
            <span>Ep: {{ $review->episode_watched }}</span>
            <p>{{ $review->review_text }}</p>

            <form method="POST" action="{{ route('watchitem.progress.destroy', $review->watch_items_id) }}"
                class="review-card-actions">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"
                    data-confirm
                    data-title="Hapus dari History?"
                    data-text="Progress, rating, dan review milikmu untuk {{ $review->watchItem->title }} akan dihapus permanen."
                    data-submit="Ya, Hapus">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </form>
        </div>
    @empty
        <p>Belum ada histori.</p>
    @endforelse
</div>

@endsection