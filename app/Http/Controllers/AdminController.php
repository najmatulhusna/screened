<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WatchItem;
use App\Models\Review;
use App\Models\User;
use App\Models\Admin;

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

        return view('admin.dashboard', compact(
            'watchItems',
            'typeStats',
            'totalItems',
            'totalUsers',
            'totalAdmins',
            'totalReviews',
            'avgRating'
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
            'poster' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['title','type','release_year','genre','country','director_creator','cast_list','synopsis','duration','total_episodes']);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        $watchItem = WatchItem::create($data);

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

        return view('admin.edit', compact('watchItem', 'adminReview'));
    }

    public function update(Request $request, $id)
    {
        $watchItem = WatchItem::findOrFail($id);

        $request->validate([
            'title' => 'required|max:255',
            'type' => 'required|in:Movie,Drama,Series,Tv Show',
            'release_year' => 'required|integer',
            'genre' => 'required',
        ]);

        $data = $request->only(['title','type','release_year','genre','country','director_creator','cast_list','synopsis','duration','total_episodes']);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        $watchItem->update($data);

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

    public function destroy($id)
    {
        WatchItem::findOrFail($id)->delete();
        return redirect('/admin/manage')->with('success', 'Item berhasil dihapus!');
    }

    public function apiIndex()
    {
        $watchItems = WatchItem::all()->map(function ($item) {
            return [
                'title' => $item->title,
                'type' => $item->type,
                'release_year' => $item->release_year,
                'genre' => $item->genre,
                'rating' => $item->rating ?? '-',
                'action' => '<a href="' . route('admin.edit', $item->id) . '">Edit</a>'
                    . ' <form method="POST" action="' . route('admin.destroy', $item->id) . '" style="display:inline">'
                    . csrf_field() . method_field('DELETE')
                    . '<button type="submit" onclick="return confirm(\'Yakin hapus?\')">Hapus</button>'
                    . '</form>',
            ];
        });

        return response()->json(['data' => $watchItems]);
    }
}