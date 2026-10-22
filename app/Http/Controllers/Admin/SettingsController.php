<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings', [
            'settings' => SettingsService::all(),
            'midtransConfigured' => (bool) config('services.midtrans.server_key'),
            'midtransProduction' => (bool) config('services.midtrans.is_production'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'vip_price' => 'required|integer|min:1000|max:100000000',
            'vip_days' => 'required|integer|min:1|max:3650',
        ]);

        SettingsService::save([
            'vip_price' => (int) $data['vip_price'],
            'vip_days' => (int) $data['vip_days'],
        ]);

        return back()->with('success', 'Pengaturan VIP berhasil disimpan.');
    }
}
