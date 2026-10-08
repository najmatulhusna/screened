<?php

namespace App\Observers;

use App\Models\Review;
use App\Services\TelegramNotifier;

class ReviewObserver
{
    public function created(Review $review): void
    {
        if ($this->hasContent($review)) {
            $this->notify($review);
        }
    }

    public function updated(Review $review): void
    {
        if (!$review->wasChanged(['rating', 'review_text'])) {
            return;
        }

        if ($this->hasContent($review)) {
            $this->notify($review);
        }
    }

    private function hasContent(Review $review): bool
    {
        return $review->rating !== null || filled($review->review_text);
    }

    private function notify(Review $review): void
    {
        $item = $review->watchItem;
        $by = $review->user?->username ?? $review->admin?->username ?? '-';
        $url = route('watchitem.detail', $review->watch_items_id);

        $text = "<b>\u{2B50} REVIEW BARU</b>\n\n"
            . "<b>Judul:</b> ".e($item?->title ?? '-')."\n"
            . "<b>Oleh:</b> ".e($by)."\n"
            . "<b>Status:</b> ".e($review->status)." | <b>Ep:</b> ".(int) $review->episode_watched."\n"
            . "<b>Rating:</b> ".($review->rating !== null ? $review->rating.'/10' : '-')."\n\n"
            . e($review->review_text ?: '(tanpa teks review)')."\n\n"
            . "<a href=\"".e($url)."\">Buka di SCREENED</a>";

        TelegramNotifier::send($text, (int) config('services.telegram.thread_review'));
    }
}
