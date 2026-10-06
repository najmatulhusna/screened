<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WatchItem;
use App\Models\Review;

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

        return view('watch_items.detail', compact('watchItem', 'userReview', 'isFavorited', 'adminReviews', 'userReviews', 'canTrack'));
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
            'rating' => 'required|numeric|min:1|max:10',
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

        $review->fill([
            'episode_watched' => $request->filled('episode_watched') ? (int) $request->episode_watched : 0,
            'status' => $request->status,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
        ]);
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

    public function statistics()
    {
        $isUser = auth()->check();
        $isAdmin = session()->has('admin_id');

        if (!$isUser && !$isAdmin) {
            return redirect('/login');
        }

        // hitung progress milik user dan/atau admin yang sedang login
        $reviews = Review::with('watchItem')
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

        $totalWatched = $reviews->where('status', 'Completed')->count();
        $totalWatching = $reviews->where('status', 'Watching')->count();
        $totalPlan = $reviews->where('status', 'Plan to Watch')->count();
        $avgRating = $reviews->whereNotNull('rating')->avg('rating');

        return view('user.statistics', compact(
            'reviews', 'totalWatched', 'totalWatching', 'totalPlan', 'avgRating'
        ));
    }
}