<?php

namespace App\Http\Controllers;

use App\Models\WatchItem;

class WatchController extends Controller
{
    /** Durasi preview gratis film (detik) untuk user non-VIP. */
    private const PREVIEW_SECONDS = 600;

    public function show($id, $ep = null)
    {
        $watchItem = WatchItem::findOrFail($id);
        $isMovie = $watchItem->isMovie();

        if ($isMovie) {
            $episode = null;
            $episodeNumber = 0;
        } else {
            $episode = max(1, (int) ($ep ?? 1));
            $episodeNumber = $episode;

            $total = (int) $watchItem->total_episodes;
            if ($total > 0 && $episode > $total) {
                return redirect()->route('watch', ['id' => $watchItem->id, 'ep' => $total]);
            }
        }

        $isAdmin = session()->has('admin_id');
        $isVip = auth()->check() && auth()->user()->isVip();
        $hasFullAccess = $isAdmin || $isVip;

        $streamUrl = $watchItem->streamUrl($episodeNumber);

        // status akses untuk halaman nonton
        $access = 'full';
        $previewRemaining = null;
        $paywallNow = false;
        $autoOpenPaywall = false;

        if (! $hasFullAccess) {
            if ($isMovie) {
                // preview 10 menit, timer disimpan di session supaya refresh tidak reset
                $sessionKey = 'preview_started_'.$watchItem->id;
                if (! session()->has($sessionKey)) {
                    session([$sessionKey => now()->timestamp]);
                }

                $elapsed = now()->timestamp - (int) session($sessionKey);
                $previewRemaining = max(0, self::PREVIEW_SECONDS - $elapsed);
                $access = $previewRemaining > 0 ? 'preview' : 'locked';
                $paywallNow = $previewRemaining <= 0;
            } elseif ($episodeNumber === 1) {
                // serial: Ep 1 bisa ditonton, popup bayar dibuka otomatis (bisa ditutup)
                $access = 'episode1';
                $autoOpenPaywall = true;
            } else {
                $access = 'locked';
                $paywallNow = true;
            }
        }

        // daftar episode (serial) untuk navigasi
        $episodes = [];
        if (! $isMovie) {
            $maxEpisode = max(
                (int) $watchItem->total_episodes,
                (int) $watchItem->streamLinks()->max('episode_number')
            );

            for ($i = 1; $i <= $maxEpisode; $i++) {
                $episodes[$i] = [
                    'url' => $watchItem->streamUrl($i),
                    'locked' => ! $hasFullAccess && $i > 1,
                    'current' => $i === $episodeNumber,
                ];
            }
        }

        $streamMissing = ! $streamUrl;

        return view('watch_items.watch', [
            'watchItem' => $watchItem,
            'episode' => $episode,
            'episodeNumber' => $episodeNumber,
            'streamUrl' => $streamUrl,
            'streamMissing' => $streamMissing,
            'isMovie' => $isMovie,
            'isVip' => $isVip,
            'hasFullAccess' => $hasFullAccess,
            'access' => $access,
            'previewRemaining' => $previewRemaining,
            'paywallNow' => $paywallNow && ! $streamMissing,
            'autoOpenPaywall' => $autoOpenPaywall && ! $streamMissing,
            'episodes' => $episodes,
        ]);
    }
}
