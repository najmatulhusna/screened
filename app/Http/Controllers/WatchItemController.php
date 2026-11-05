<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WatchItem;
use App\Models\Review;
use App\Models\UserWatchlist;

class WatchItemController extends Controller
{
    public function index(Request $request)
    {
        $query = WatchItem::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('genre')) {
            $query->where('genre', 'like', '%' . $request->genre . '%');
        }

        if ($request->filled('year')) {
            $query->where('release_year', $request->year);
        }

        switch ($request->sort) {
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'year':
                $query->orderBy('release_year', 'desc');
                break;
            default:
                $query->latest();
        }

        $watchItems = $query->get();

        $genres = WatchItem::pluck('genre')
            ->flatMap(fn($g) => explode(', ', $g))
            ->unique()
            ->sort()
            ->values();

        return view('watch_items.index', compact('watchItems', 'genres'));
    }

    public function detail($id)
    {
        $watchItem = WatchItem::with('reviews.user', 'reviews.admin')->findOrFail($id);
        $isFavorited = false;

        $adminReviews = $watchItem->reviews->filter(fn($r) => $r->admin_id !== null)->values();
        $userReviews = $watchItem->reviews->filter(fn($r) => $r->users_id !== null)->sortByDesc('created_at')->values();

        if (auth()->check()) {
            $favorites = auth()->user()->data ? json_decode(auth()->user()->data, true) : [];
            $favoriteIds = $favorites['favorites'] ?? [];
            $isFavorited = in_array($id, $favoriteIds);
        }

        // progress tampil otomatis: milik user yang login, atau milik admin yang sedang login
        $userReview = $this->currentReview($id);
        $canTrack = auth()->check() || session()->has('admin_id');

        // status tombol "Add to My Album" (hanya untuk user login)
        $isVipUser = (bool) (auth()->check() && auth()->user()->isVip());
        $inAlbum = auth()->check()
            && UserWatchlist::where('user_id', auth()->id())
                ->where('watch_item_id', $id)
                ->exists();

        return view('watch_items.detail', compact(
            'watchItem',
            'userReview',
            'isFavorited',
            'adminReviews',
            'userReviews',
            'canTrack',
            'isVipUser',
            'inAlbum'
        ));
    }

    /** Review yang dipakai form progress: milik user dulu, kalau tidak ada pakai milik admin. */
    private function currentReview($watchItemId): ?Review
    {
        if (auth()->check()) {
            $review = Review::where('watch_items_id', $watchItemId)
                ->where('users_id', auth()->id())
                ->first();

            if ($review) {
                return $review;
            }
        }

        if (session()->has('admin_id')) {
            return Review::where('watch_items_id', $watchItemId)
                ->where('admin_id', session('admin_id'))
                ->first();
        }

        return null;
    }

    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Plan to Watch,Watching,Completed',
            'rating' => 'nullable|numeric|min:1|max:10',
            'review_text' => 'nullable|string',
            'episode_watched' => 'nullable|integer|min:0',
        ]);

        $review = $this->currentReview($id);

        if (!$review) {
            if (auth()->check()) {
                $review = new Review(['watch_items_id' => $id, 'users_id' => auth()->id()]);
            } elseif (session()->has('admin_id')) {
                $review = new Review(['watch_items_id' => $id, 'admin_id' => session('admin_id')]);
            } else {
                return redirect('/login');
            }
        }

        $data = [
            'episode_watched' => $request->filled('episode_watched') ? (int) $request->episode_watched : 0,
            'status' => $request->status,
        ];

        // rating & review_text hanya dikirim form Review; form Progress tidak
        // mengirimnya supaya progres tidak menimpa rating yang sudah ada
        if ($request->has('rating')) {
            $data['rating'] = $request->filled('rating') ? $request->rating : null;
        }

        if ($request->has('review_text')) {
            $data['review_text'] = $request->filled('review_text') ? $request->review_text : null;
        }

        $review->fill($data);
        $review->save();

        return back()->with('success', 'Review berhasil disimpan!');
    }

    public function toggleFavorite($id)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();
        $favorites = $user->data ? json_decode($user->data, true) : [];
        $favorites = $favorites['favorites'] ?? [];

        if (in_array($id, $favorites)) {
            $favorites = array_values(array_diff($favorites, [$id]));
        } else {
            $favorites[] = $id;
        }

        $data = $user->data ? json_decode($user->data, true) : [];
        $data['favorites'] = $favorites;
        $user->data = json_encode($data);
        $user->save();

        return back();
    }

    public function favorites()
    {
        $favoriteIds = [];

        if (auth()->check()) {
            $user = auth()->user();
            $data = $user->data ? json_decode($user->data, true) : [];
            $favoriteIds = $data['favorites'] ?? [];
        }

        $watchItems = WatchItem::whereIn('id', $favoriteIds)->get();

        // progress tampil juga untuk admin yang sedang login, bukan hanya user
        $userReviews = Review::whereIn('watch_items_id', $favoriteIds)
            ->where(function ($query) {
                if (auth()->check()) {
                    $query->where('users_id', auth()->id());
                }
                if (session()->has('admin_id')) {
                    $query->orWhere('admin_id', session('admin_id'));
                }
            })
            ->get()
            ->groupBy('watch_items_id')
            ->map(fn ($group) => $group->firstWhere('users_id', auth()->id()) ?? $group->first());

        return view('user.favorites', compact('watchItems', 'userReviews'));
    }

    public function destroyFavorite($id)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        $data = $user->data ? json_decode($user->data, true) : [];
        $favorites = $data['favorites'] ?? [];
        $data['favorites'] = array_values(array_diff($favorites, [$id]));

        $user->data = json_encode($data);
        $user->save();

        return back()->with('success', 'Dihapus dari Favorites. Progress & review tidak ikut terhapus.');
    }

    public function destroyProgress($id)
    {
        $review = $this->currentReview($id);

        if (!$review) {
            return back()->with('error', 'Progress tidak ditemukan atau sudah dihapus.');
        }

        $review->delete();

        return back()->with('success', 'Progress & review berhasil dihapus.');
    }

    /**
     * Status tontonan yang ditampilkan pada bagian Watch Distribution.
     * (On Hold & Dropped belum ada di enum reviews, tetap ditampilkan dengan angka 0.)
     */
    private const WATCH_STATUSES = ['Completed', 'Watching', 'Plan to Watch', 'On Hold', 'Dropped'];

    public function statistics(Request $request)
    {
        $isUser = auth()->check();
        $isAdmin = session()->has('admin_id');

        if (!$isUser && !$isAdmin) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Status VIP dicek dari masa aktif subscription (users.vip_until), bukan dari badge di navbar.
        $isVip = $isAdmin || (bool) ($user && $user->isVip());

        // Satu query + eager loading judul (hindari N+1), selalu difilter user/admin yang login.
        $reviews = Review::with('watchItem:id,title,genre')
            ->where(function ($query) use ($isUser, $isAdmin) {
                if ($isUser) {
                    $query->where('users_id', auth()->id());
                }
                if ($isAdmin) {
                    $query->orWhere('admin_id', session('admin_id'));
                }
            })
            ->latest()
            ->get();

        // ---------- Ringkasan (Free & VIP) ----------
        $userData = ($user && $user->data) ? json_decode($user->data, true) : [];
        $favoriteIds = array_values(array_unique(array_filter($userData['favorites'] ?? [], 'is_numeric')));

        // Sumber statistik utama: My Album (user_watchlists), selalu difilter
        // user yang login (satu query, tanpa N+1). Bila user belum punya album
        // sama sekali, pakai data reviews (progress) lama sebagai fallback
        // agar halaman tidak kosong. My History tetap bersumber dari reviews.
        $albumItems = $isUser
            ? UserWatchlist::where('user_id', auth()->id())->get([
                'id', 'watch_status', 'genre', 'personal_rating',
                'date_completed', 'created_at', 'updated_at',
            ])
            : collect();

        $useAlbum = $albumItems->count() > 0;

        $totalTitles = $useAlbum ? $albumItems->count() : $reviews->count();
        $totalWatched = $useAlbum
            ? $albumItems->where('watch_status', 'Completed')->count()
            : $reviews->where('status', 'Completed')->count();
        $totalWatching = $useAlbum
            ? $albumItems->where('watch_status', 'Watching')->count()
            : $reviews->where('status', 'Watching')->count();
        $totalPlan = $useAlbum
            ? $albumItems->where('watch_status', 'Plan to Watch')->count()
            : $reviews->where('status', 'Plan to Watch')->count();
        $totalFavorites = count($favoriteIds);
        $totalReviewText = $reviews->whereNotNull('review_text')->count();

        $avgRating = $reviews->whereNotNull('rating')->avg('rating');
        $avgRating = $avgRating ? round((float) $avgRating, 1) : null;

        // Jumlah judul pada My History (timeline & daftar) tetap dari reviews.
        $historyTitles = $reviews->count();

        $hasWatchData = $totalTitles > 0;

        // Baris statistik seragam agar blok VIP (distribution/genre/monthly/recap)
        // bisa memakai satu sumber yang sama: My Album bila ada, else reviews.
        // Field: status, genre, rating, completed_at, created_at, updated_at.
        $statRows = $useAlbum
            ? $albumItems->map(fn ($a) => (object) [
                'status' => $a->watch_status,
                'genre' => $a->genre,
                'rating' => $a->personal_rating,
                'completed_at' => $a->watch_status === 'Completed' ? $a->date_completed : null,
                'created_at' => $a->created_at,
                'updated_at' => $a->updated_at,
            ])
            : $reviews->map(fn ($r) => (object) [
                'status' => $r->status,
                'genre' => $r->watchItem?->genre,
                'rating' => $r->rating,
                'completed_at' => $r->status === 'Completed' ? $r->updated_at : null,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);

        // ---------- Activity timeline (My History) ----------
        // Sumber: tabel reviews saja (tanpa tabel log baru).
        //  - created_at = judul ditambahkan ke My Album
        //  - updated_at = update terakhir (status/progress/rating/review)
        // Data favorit disimpan di JSON users.data tanpa timestamp, jadi tidak
        // dimasukkan ke timeline agar tanggal yang tampil tetap akurat.
        $activities = [];

        foreach ($reviews as $review) {
            $title = $review->watchItem?->title ?? 'Judul tanpa nama';
            $hasLaterUpdate = $review->created_at
                && $review->updated_at
                && $review->updated_at->greaterThan($review->created_at);

            // Aktivitas 1: judul ditambahkan ke My Album.
            $addedParts = ['Ditambahkan ke My Album'];
            if (! $hasLaterUpdate) {
                // Tanpa update lanjutan, isi rating/review sudah pasti terisi saat pencatatan awal.
                if ($review->rating !== null) {
                    $addedParts[] = 'Rating ' . $review->rating . '/10';
                }
                if ($review->review_text !== null && $review->review_text !== '') {
                    $addedParts[] = 'Menulis review';
                }
            }

            $activities[] = [
                'icon' => 'bi-collection-play-fill',
                'title' => $title,
                'description' => implode(' · ', $addedParts),
                'date' => $review->created_at,
                'item_id' => $review->watch_items_id,
            ];

            // Aktivitas 2: update terakhir (status/progress/rating/review berubah).
            if ($hasLaterUpdate) {
                if ($review->status === 'Completed') {
                    $desc = ['Menyelesaikan tontonan'];
                } elseif ($review->status === 'Watching') {
                    $desc = ['Memperbarui progress tontonan'];
                } else {
                    $desc = ['Mengubah status menjadi ' . $review->status];
                }

                if ((int) $review->episode_watched > 0) {
                    $desc[] = 'Ep ' . (int) $review->episode_watched;
                }
                if ($review->rating !== null) {
                    $desc[] = 'Rating ' . $review->rating . '/10';
                }
                if ($review->review_text !== null && $review->review_text !== '') {
                    $desc[] = 'Menulis review';
                }

                $activities[] = [
                    'icon' => $review->status === 'Completed' ? 'bi-check2-circle' : 'bi-arrow-repeat',
                    'title' => $title,
                    'description' => implode(' · ', $desc),
                    'date' => $review->updated_at,
                    'item_id' => $review->watch_items_id,
                ];
            }
        }

        // Aktivitas terbaru di atas; batas aman agar HTML tidak membengkak.
        usort($activities, fn ($a, $b) => $b['date']->getTimestamp() <=> $a['date']->getTimestamp());

        $activityCount = count($activities);
        $activities = array_slice($activities, 0, 60);

        // ---------- Data VIP (Watch Distribution, Genre, Monthly, Recap) ----------
        $distribution = collect();
        $genres = collect();
        $monthly = array_fill(0, 12, 0);
        $years = collect();
        $year = (int) now()->year;
        $yearTotal = 0;
        $recap = ['completed' => 0, 'added' => 0, 'avg_rating' => null];

        if ($isVip) {
            $distribution = collect(self::WATCH_STATUSES)
                ->map(function ($status) use ($statRows, $totalTitles) {
                    $count = $statRows->where('status', $status)->count();

                    return [
                        'status' => $status,
                        'count' => $count,
                        'percent' => $totalTitles ? round($count * 100 / $totalTitles, 1) : 0,
                    ];
                })
                ->values();

            // Genre paling sering ditonton (kolom genre dipisah koma).
            $genreCounts = [];
            foreach ($statRows as $row) {
                $genre = $row->genre;
                if (!$genre) {
                    continue;
                }

                foreach (explode(',', $genre) as $name) {
                    $name = trim($name);
                    if ($name !== '') {
                        $genreCounts[$name] = ($genreCounts[$name] ?? 0) + 1;
                    }
                }
            }
            arsort($genreCounts);

            $genres = collect($genreCounts)
                ->take(10)
                ->map(fn ($count, $name) => ['genre' => $name, 'count' => $count])
                ->values();

            // Tahun yang tersedia untuk filter: tahun judul Completed + tahun sekarang.
            $currentYear = (int) now()->year;
            $years = $statRows
                ->filter(fn ($row) => $row->status === 'Completed')
                ->map(fn ($row) => optional($row->completed_at)->year)
                ->filter()
                ->unique()
                ->sortDesc()
                ->values();

            if (!$years->contains($currentYear)) {
                $years->push($currentYear);
                $years = $years->sortDesc()->values();
            }

            // Validasi input filter tahun (tolak nilai bukan string 4 digit, mis. ?year[]=..).
            $yearInput = $request->query('year');
            if ($yearInput !== null) {
                $validYear = is_string($yearInput) && preg_match('/^\d{4}$/', $yearInput) === 1;

                if ($validYear) {
                    $validYear = (int) $yearInput >= 2000 && (int) $yearInput <= $currentYear;
                }

                if (! $validYear) {
                    return redirect()->route('statistics')->with('error', 'Filter tahun tidak valid.');
                }

                $year = (int) $yearInput;
            }

            // Monthly Watch Activity: judul Completed per bulan pada tahun terpilih.
            // Sumber tanggal: date_completed (My Album) atau updated_at (fallback reviews).
            foreach ($statRows as $row) {
                if ($row->status !== 'Completed' || !$row->completed_at) {
                    continue;
                }
                if ((int) $row->completed_at->year === $year) {
                    $monthly[(int) $row->completed_at->month - 1]++;
                }
            }
            $yearTotal = array_sum($monthly);

            // Ringkasan tahunan (Yearly Watch Recap).
            $addedInYear = $statRows
                ->filter(fn ($row) => $row->created_at && (int) $row->created_at->year === $year)
                ->count();
            $ratedInYear = $statRows->filter(
                fn ($row) => $row->rating !== null
                    && $row->updated_at
                    && (int) $row->updated_at->year === $year
            );

            $recap = [
                'completed' => $yearTotal,
                'added' => $addedInYear,
                'avg_rating' => $ratedInYear->count()
                    ? round((float) $ratedInYear->avg('rating'), 1)
                    : null,
            ];
        }

        return view('user.statistics', compact(
            'reviews',
            'activities',
            'activityCount',
            'totalTitles',
            'historyTitles',
            'totalWatched',
            'totalWatching',
            'totalPlan',
            'totalFavorites',
            'totalReviewText',
            'avgRating',
            'hasWatchData',
            'isVip',
            'distribution',
            'genres',
            'monthly',
            'years',
            'year',
            'yearTotal',
            'recap'
        ));
    }
}