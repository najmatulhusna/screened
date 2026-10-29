<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WatchItem;
use App\Models\Review;
use App\Models\User;
use App\Models\Admin;
use App\Models\Transaction;

class AdminController extends Controller
{
    /** warna small-box + ikon untuk tiap tipe */
    private const TYPE_META = [
        'Movie'   => ['color' => 'success', 'icon' => 'bi-camera-reels'],
        'Drama'   => ['color' => 'warning', 'icon' => 'bi-tv'],
        'Series'  => ['color' => 'primary', 'icon' => 'bi-collection-play'],
        'Tv Show' => ['color' => 'danger',  'icon' => 'bi-broadcast'],
    ];

    public function dashboard()
    {
        $watchItems = WatchItem::latest()->get();

        // jumlah per tipe, semua tipe pada enum tetap ditampilkan walau 0
        $typeTotals = WatchItem::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type');

        $allTypes = collect(array_keys(self::TYPE_META))
            ->merge($typeTotals->keys())
            ->unique()
            ->values();

        $typeStats = $allTypes->map(function ($type) use ($typeTotals) {
            $meta = self::TYPE_META[$type] ?? ['color' => 'dark', 'icon' => 'bi-collection'];

            return [
                'type'    => $type,
                'total'   => (int) ($typeTotals[$type] ?? 0),
                'percent' => 0,
                'color'   => $meta['color'],
                'icon'    => $meta['icon'],
            ];
        });

        $totalItems = (int) $typeTotals->sum();

        // persentase tiap tipe terhadap total
        $typeStats = $typeStats->map(function ($stat) use ($totalItems) {
            $stat['percent'] = $totalItems > 0
                ? round($stat['total'] / $totalItems * 100, 1)
                : 0;

            return $stat;
        })->values();

        // jumlah review per status
        $totalUsers = User::count();
        $totalAdmins = Admin::count();
        $totalReviews = Review::count();
        $avgRating = (float) WatchItem::whereNotNull('rating')->avg('rating');

        // ringkasan transaksi + 5 transaksi terbaru
        $txStats = [
            'success_amount' => (float) Transaction::where('status', Transaction::STATUS_SUCCESS)->sum('amount'),
            'success_count'  => Transaction::where('status', Transaction::STATUS_SUCCESS)->count(),
            'pending_count'  => Transaction::where('status', Transaction::STATUS_PENDING)->count(),
            'total_count'    => Transaction::count(),
        ];
        $recentTransactions = Transaction::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'watchItems',
            'typeStats',
            'totalItems',
            'totalUsers',
            'totalAdmins',
            'totalReviews',
            'avgRating',
            'txStats',
            'recentTransactions'
        ));
    }

    public function manage()
    {
        $watchItems = WatchItem::latest()->get();
        return view('admin.manage', compact('watchItems'));
    }

    public function add()
    {
        return view('admin.add');
    }

    public function destroyReview($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return back()->with('success', 'Review & rating berhasil dihapus!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'type' => 'required|in:Movie,Drama,Series,Tv Show',
            'release_year' => 'required|integer',
            'genre' => 'required',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg|max:500',
            'trailer_url' => 'nullable|string|max:2000',
            'stream_links' => 'nullable|array',
            'stream_links.*.episode_number' => 'required|integer|min:0',
            'stream_links.*.url' => 'nullable|string|max:2000',
        ]);

        $data = $request->only(['title','type','release_year','genre','country','director_creator','cast_list','synopsis','trailer_url','duration','total_episodes']);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        $watchItem = WatchItem::create($data);

        $this->syncStreamLinks($watchItem, $request);

        Review::create([
            'watch_items_id' => $watchItem->id,
            'admin_id' => session('admin_id'),
            'status' => $request->status ?? 'Plan to Watch',
            'episode_watched' => $request->episode_watched ?? 0,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
        ]);

        return redirect('/admin/manage')->with('success', 'Item berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $watchItem = WatchItem::findOrFail($id);

        // review milik admin yang sedang login, untuk mengisi form "Your Review"
        $adminReview = $watchItem->reviews()
            ->where('admin_id', session('admin_id'))
            ->first();

        // map stream link: {episode_number: url}
        $streamLinksMap = $watchItem->streamLinks()
            ->get()
            ->mapWithKeys(fn ($link) => [$link->episode_number => $link->url])
            ->all();

        return view('admin.edit', compact('watchItem', 'adminReview', 'streamLinksMap'));
    }

    public function update(Request $request, $id)
    {
        $watchItem = WatchItem::findOrFail($id);

        $request->validate([
            'title' => 'required|max:255',
            'type' => 'required|in:Movie,Drama,Series,Tv Show',
            'release_year' => 'required|integer',
            'genre' => 'required',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg|max:500',
            'trailer_url' => 'nullable|string|max:2000',
            'stream_links' => 'nullable|array',
            'stream_links.*.episode_number' => 'required|integer|min:0',
            'stream_links.*.url' => 'nullable|string|max:2000',
        ]);

        $data = $request->only(['title','type','release_year','genre','country','director_creator','cast_list','synopsis','trailer_url','duration','total_episodes']);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        $watchItem->update($data);

        $this->syncStreamLinks($watchItem, $request);

        Review::updateOrCreate(
            [
                'watch_items_id' => $id,
                'admin_id' => session('admin_id'),
            ],
            [
                'status' => $request->status ?? 'Plan to Watch',
                'episode_watched' => $request->episode_watched ?? 0,
                'rating' => $request->rating,
                'review_text' => $request->review_text,
            ]
        );

        return redirect('/admin/manage')->with('success', 'Item berhasil diupdate!');
    }

    /**
     * Sinkronkan input stream_links dari form ke tabel stream_links.
     * - URL kosong / baris hilang -> link episode dihapus
     * - form yang tidak mengirim stream_links (mis. JS mati) -> data lama dipertahankan
     */
    private function syncStreamLinks(WatchItem $watchItem, Request $request): void
    {
        if (!$request->has('stream_links') || !is_array($request->stream_links)) {
            return;
        }

        $keep = [];

        foreach ($request->stream_links as $row) {
            $episode = (int) ($row['episode_number'] ?? -1);
            $url = trim((string) ($row['url'] ?? ''));

            if ($episode < 0 || $url === '') {
                continue;
            }

            $keep[$episode] = $url;
        }

        $watchItem->streamLinks()
            ->whereNotIn('episode_number', array_keys($keep))
            ->delete();

        foreach ($keep as $episode => $url) {
            $watchItem->streamLinks()->updateOrCreate(
                ['episode_number' => $episode],
                ['url' => $url]
            );
        }
    }

    public function destroy($id)
    {
        WatchItem::findOrFail($id)->delete();
        return redirect('/admin/manage')->with('success', 'Item berhasil dihapus!');
    }

    public function apiIndex()
    {
        $watchItems = WatchItem::all()->map(function ($item) {
            $adminReview = $item->reviews()
                ->where('admin_id', session('admin_id'))
                ->first();

            return [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type,
                'release_year' => $item->release_year,
                'genre' => $item->genre,
                'country' => $item->country,
                'director_creator' => $item->director_creator,
                'cast_list' => $item->cast_list,
                'duration' => $item->duration,
                'total_episodes' => $item->total_episodes,
                'synopsis' => $item->synopsis,
                'poster' => $item->poster ? asset('storage/' . $item->poster) : null,
                'trailer_url' => $item->trailer_url,
                'stream_links' => $item->streamLinks()
                    ->get()
                    ->mapWithKeys(fn ($link) => [$link->episode_number => $link->url])
                    ->all(),
                'rating' => $item->rating,
                'review_rating' => $adminReview->rating ?? '',
                'status' => $adminReview->status ?? 'Plan to Watch',
                'episode_watched' => $adminReview->episode_watched ?? 0,
                'review_text' => $adminReview->review_text ?? '',
                'action' => '<button type="button" class="btn-edit" data-id="' . $item->id . '">Edit</button>'
                    . ' <form method="POST" action="' . route('admin.destroy', $item->id) . '" style="display:inline">'
                    . csrf_field() . method_field('DELETE')
                    . '<button type="submit" onclick="return confirm(\'Yakin hapus?\')">Hapus</button>'
                    . '</form>',
            ];
        });

        return response()->json(['data' => $watchItems]);
    }
}