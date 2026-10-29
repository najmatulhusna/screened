<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VipPackage;
use Illuminate\Http\Request;

/** Kelola paket VIP (harga + berlaku berapa hari) di halaman Transaksi. */
class VipPackageController extends Controller
{
    /** Tambah paket VIP baru. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60',
            'price' => 'required|integer|min:1000|max:1000000000',
            'duration_days' => 'required|integer|min:1|max:3650',
        ]);

        $package = VipPackage::create($data + ['is_active' => true]);

        return back()->with(
            'success',
            'Paket "' . $package->name . '" ditambahkan — Rp ' . number_format($package->price, 0, ',', '.')
            . ' berlaku ' . $package->duration_days . ' hari.'
        );
    }

    /** Hapus paket VIP. */
    public function destroy($id)
    {
        $package = VipPackage::findOrFail($id);
        $package->delete();

        return back()->with('success', 'Paket "' . $package->name . '" dihapus.');
    }
}
