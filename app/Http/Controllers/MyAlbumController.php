<?php

namespace App\Http\Controllers;

use App\Models\UserWatchlist;
use App\Models\WatchItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * My Album: album tontonan pribadi (fitur VIP).
 *
 * Aturan akses:
 *  - Halaman bisa dibuka guest & Free user (read-only / state terkunci).
 *  - Semua aksi tulis wajib login + VIP aktif, dicek di backend (bukan cuma di view).
 *  - Selalu dibatasi user_id yang login: tidak ada akses ke album user lain.
 *  - Data tidak pernah dihapus otomatis saat VIP habis.
 */
class MyAlbumController extends Controller
{
    /** Cegah aksi tulis oleh non-VIP. Null = diperbolehkan. */
    private function denyIfNotVip()
    {
        $user = auth()->user();

        if ($user && $user->isVip()) {
            return null;
        }

        $message = $user && $user->vip_until
            ? 'VIP kamu sudah berakhir. Perpanjang VIP untuk mengelola My Album.'
            : 'My Album adalah fitur VIP. Upgrade dulu untuk mengelola album.';

        return back()->with('error', $message);
    }

    /** Semua baris album milik user yang login (null = tidak punya akses). */
    private function ownEntry(int $id): UserWatchlist
    {
        return UserWatchlist::where('user_id', auth()->id())->findOrFail($id);
    }

    /** ------------------------------------------------------------------ */
    /** Halaman utama (guest, Free, VIP)                                    */
    /** ------------------------------------------------------------------ */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isVip = (bool) ($user && $user->isVip());
        $vipExpired = (bool) ($user && ! $isVip && $user->vip_until !== null);

        $items = collect();
        $allItems = collect();
        $summary = ['total' => 0, 'completed' => 0, 'watching' => 0, 'plan' => 0];
        $genres = collect();
        $pickerItems = collect();

        if ($user) {
            // Satu query saja; filter & ringkasan dihitung in-memory (album pribadi kecil).
            $allItems = UserWatchlist::where('user_id', $user->id)
                ->with('watchItem:id,title')
                ->get();

            $summary = [
                'total' => $allItems->count(),
                'completed' => $allItems->where('watch_status', 'Completed')->count(),
                'watching' => $allItems->where('watch_status', 'Watching')->count(),
                'plan' => $allItems->where('watch_status', 'Plan to Watch')->count(),
            ];

            $genres = $allItems->pluck('genre')
                ->flatMap(fn ($g) => explode(',', (string) $g))
                ->map(fn ($g) => trim($g))
                ->filter()
                ->unique()
                ->sort()
                ->values();

            // ---- Filter ----
            $items = $allItems;

            if ($request->filled('search')) {
                $needle = strtolower($request->query('search'));
                $items = $items->filter(fn ($i) => str_contains(strtolower($i->title), $needle));
            }

            if ($request->filled('type')) {
                $items = $items->filter(fn ($i) => $i->type === $request->query('type'));
            }

            if ($request->filled('genre')) {
                $items = $items->filter(
                    fn ($i) => str_contains(strtolower((string) $i->genre), strtolower($request->query('genre')))
                );
            }

            if ($request->filled('status') && in_array($request->query('status'), UserWatchlist::STATUSES, true)) {
                $items = $items->filter(fn ($i) => $i->watch_status === $request->query('status'));
            }

            $items = match ($request->query('sort')) {
                'title' => $items->sortBy('title', SORT_STRING),
                'oldest' => $items->sortBy('created_at'),
                default => $items->sortByDesc('created_at'),
            };
            $items = $items->values();

            // ---- Pilihan judul Collection (khusus VIP) ----
            if ($isVip) {
                // Sembunyikan judul yang sudah ada di album (cegah duplikat).
                $alreadyIds = $allItems->pluck('watch_item_id')->filter()->all();

                $picker = WatchItem::query()->orderBy('title');

                if ($alreadyIds) {
                    $picker->whereNotIn('id', $alreadyIds);
                }

                if ($request->filled('picker_search')) {
                    $picker->where('title', 'like', '%' . $request->query('picker_search') . '%');
                }

                $pickerItems = $picker->limit(40)->get(['id', 'title', 'type', 'release_year', 'genre', 'poster']);
            }
        }

        return view('user.my-album', compact(
            'user',
            'isVip',
            'vipExpired',
            'items',
            'allItems',
            'summary',
            'genres',
            'pickerItems'
        ));
    }

    /** ------------------------------------------------------------------ */
    /** Add from Collection (dari halaman detail Collection / picker)        */
    /** ------------------------------------------------------------------ */
    public function storeFromCollection(Request $request, int $watchItemId)
    {
        if ($denied = $this->denyIfNotVip()) {
            return $denied;
        }

        $watchItem = WatchItem::findOrFail($watchItemId);
        $userId = auth()->id();

        $already = UserWatchlist::where('user_id', $userId)
            ->where('watch_item_id', $watchItem->id)
            ->exists();

        if ($already) {
            return back()->with('error', '"' . $watchItem->title . '" sudah ada di My Album kamu.');
        }

        try {
            DB::transaction(function () use ($watchItem, $userId) {
                UserWatchlist::create([
                    'user_id' => $userId,
                    'watch_item_id' => $watchItem->id,
                    // snapshot data Collection, tidak menyalin apapun ke watch_items
                    'title' => $watchItem->title,
                    'type' => $watchItem->type,
                    'release_year' => $watchItem->release_year,
                    'genre' => $watchItem->genre,
                    'country' => $watchItem->country,
                    'director_creator' => $watchItem->director_creator,
                    'cast_list' => $watchItem->cast_list,
                    'synopsis' => $watchItem->synopsis,
                    'poster' => $watchItem->poster,
                    'duration' => $watchItem->duration,
                    'total_episodes' => $watchItem->total_episodes,
                    'watch_status' => 'Plan to Watch',
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique (user_id, watch_item_id) menjadi jaring pengaman terakhir.
            return back()->with('error', '"' . $watchItem->title . '" sudah ada di My Album kamu.');
        }

        return back()->with('success', '"' . $watchItem->title . '" ditambahkan ke My Album.');
    }

    /** ------------------------------------------------------------------ */
    /** Add Manually                                                        */
    /** ------------------------------------------------------------------ */
    public function create()
    {
        if ($denied = $this->denyIfNotVip()) {
            return $denied;
        }

        return view('user.my-album-form', [
            'entry' => null,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        if ($denied = $this->denyIfNotVip()) {
            return $denied;
        }

        $data = $this->validateEntry($request);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        } else {
            unset($data['poster']);
        }

        $data['user_id'] = auth()->id();
        $data['watch_item_id'] = null; // entri manual tidak terhubung Collection

        UserWatchlist::create($data);

        return redirect()->route('myalbum.index')->with('success', 'Berhasil ditambahkan ke My Album.');
    }

    /** ------------------------------------------------------------------ */
    /** Detail                                                              */
    /** ------------------------------------------------------------------ */
    public function show(int $id)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $entry = $this->ownEntry($id)->load('watchItem:id,title,type');

        return view('user.my-album-detail', [
            'entry' => $entry,
            'isVip' => auth()->user()->isVip(),
        ]);
    }

    /** ------------------------------------------------------------------ */
    /** Edit                                                                */
    /** ------------------------------------------------------------------ */
    public function edit(int $id)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        // Ownership dulu: album user lain selalu 404, apa pun status VIP-nya.
        $entry = $this->ownEntry($id);

        if ($denied = $this->denyIfNotVip()) {
            return $denied;
        }

        return view('user.my-album-form', [
            'entry' => $entry,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, int $id)
    {
        // Ownership dulu: permintaan ke album user lain selalu 404.
        $entry = $this->ownEntry($id);

        if ($denied = $this->denyIfNotVip()) {
            return $denied;
        }

        $data = $this->validateEntry($request, $entry);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        } else {
            unset($data['poster']); // poster lama dipertahankan bila tidak diganti
        }

        if ($data['watch_status'] !== 'Completed') {
            $data['date_completed'] = null; // tanggal selesai hanya relevan saat Completed
        }

        $entry->update($data);

        return redirect()->route('myalbum.show', $entry->id)->with('success', 'Perubahan tersimpan.');
    }

    /** ------------------------------------------------------------------ */
    /** Remove                                                              */
    /** ------------------------------------------------------------------ */
    public function destroy(int $id)
    {
        // Ownership dulu: permintaan ke album user lain selalu 404.
        $entry = $this->ownEntry($id);

        if ($denied = $this->denyIfNotVip()) {
            return $denied;
        }

        $title = $entry->title;
        $entry->delete();

        return redirect()->route('myalbum.index')->with('success', '"' . $title . '" dihapus dari My Album.');
    }

    /** ------------------------------------------------------------------ */
    /** Validasi bersama create & update                                    */
    /** ------------------------------------------------------------------ */
    private function validateEntry(Request $request, ?UserWatchlist $entry = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(UserWatchlist::TYPES)],
            'release_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'genre' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'director_creator' => ['nullable', 'string', 'max:150'],
            'cast_list' => ['nullable', 'string', 'max:2000'],
            'synopsis' => ['nullable', 'string', 'max:5000'],
            'poster' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:500'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'total_episodes' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'watch_status' => ['required', Rule::in(UserWatchlist::STATUSES)],
            'personal_rating' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'date_started' => ['nullable', 'date'],
            'date_completed' => ['nullable', 'date', 'after_or_equal:date_started'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
