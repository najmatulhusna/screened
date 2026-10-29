<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\VipPackage;
use App\Services\MidtransService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Halaman VIP (terbuka untuk tamu & user login).
     * - Tamu / non-VIP : lihat harga + tombol beli (login dulu bila belum login)
     * - User VIP       : masa berlaku + kartu harga untuk perpanjang
     */
    public function vipPage()
    {
        $user = auth()->user();

        $price = (float) SettingsService::get('vip_price', 30000);
        $days = (int) SettingsService::get('vip_days', 30);

        // kartu yang ditampilkan: paket dari admin; kalau belum ada, pakai harga pengaturan
        $packages = VipPackage::active()->orderBy('price')->orderBy('duration_days')->get();

        if ($packages->isEmpty()) {
            $packages = collect([
                new VipPackage([
                    'name' => 'VIP SCREENED',
                    'price' => (int) $price,
                    'duration_days' => $days,
                ]),
            ]);
        }

        return view('vip', [
            'price' => $price,
            'days' => $days,
            'isVip' => (bool) ($user && $user->isVip()),
            'vipUntil' => $user?->vip_until,
            'packages' => $packages,
        ]);
    }

    /** Checkout sesi Snap untuk VIP. */
    public function checkout(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $user = auth()->user();

        // user VIP tetap boleh bayar -> masa aktifnya menumpuk (perpanjangan)
        // harga & durasi diambil dari paket VIP pilihan (atau pengaturan admin bila tanpa paket)
        $package = null;
        if ($request->filled('package_id')) {
            $package = VipPackage::active()->find($request->input('package_id'));

            if (! $package) {
                return response()->json(['message' => 'Paket VIP tidak ditemukan atau sudah nonaktif.'], 422);
            }
        }

        $price = $package ? (float) $package->price : (float) SettingsService::get('vip_price', 30000);
        $days = $package ? (int) $package->duration_days : (int) SettingsService::get('vip_days', 30);
        $planName = $package ? $package->name : 'VIP SCREENED';

        if ($price <= 0) {
            return response()->json(['message' => 'Harga VIP belum dikonfigurasi admin.'], 422);
        }

        // pakai ulang transaksi pending yang masih baru supaya order_id tidak menumpuk
        $existing = Transaction::where('users_id', $user->id)
            ->where('type', 'vip')
            ->where('status', Transaction::STATUS_PENDING)
            ->where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->first();

        $orderId = $existing?->midtrans_order_id
            ?? 'VIP-'.$user->id.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));

        $returnUrl = $request->input('return_url');
        if (is_string($returnUrl) && $returnUrl !== '') {
            session(['payment_return_url' => $returnUrl]);
        }

        try {
            $snap = app(MidtransService::class)->createSnapTransaction(
                $orderId,
                $price,
                [
                    [
                        'id' => 'vip-'.$days,
                        'price' => (int) $price,
                        'quantity' => 1,
                        'name' => $planName.' '.$days.' hari',
                    ],
                ],
                [
                    'first_name' => $user->name ?: $user->username,
                    'email' => $user->email,
                ]
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        Transaction::updateOrCreate(
            ['midtrans_order_id' => $orderId],
            [
                'users_id' => $user->id,
                'type' => 'vip',
                'amount' => $price,
                'duration_days' => $days,
                'status' => Transaction::STATUS_PENDING,
                'snap_token' => $snap['token'],
                'expires_at' => now()->addMinutes(30),
            ]
        );

        return response()->json([
            'token' => $snap['token'],
            'redirect_url' => $snap['redirect_url'],
            'order_id' => $orderId,
        ]);
    }

    /**
     * Status transaksi untuk polling dari browser.
     * Localhost tidak menerima callback Midtrans, jadi kalau masih pending
     * kita cek langsung ke API Midtrans v2.
     */
    public function status(Request $request, $orderId)
    {
        if (! auth()->check()) {
            return response()->json(['message' => 'Silakan login.'], 401);
        }

        $transaction = Transaction::where('midtrans_order_id', $orderId)
            ->where('users_id', auth()->id())
            ->firstOrFail();

        if ($transaction->status !== Transaction::STATUS_SUCCESS) {
            try {
                $remote = app(MidtransService::class)->getStatus($orderId);
            } catch (\Throwable $e) {
                // key belum terisi / Midtrans tidak terjangkau: pakai status di DB saja
                $remote = null;
            }

            if ($remote) {
                $this->applyMidtransStatus($transaction, $remote);
                $transaction->refresh();
            }
        }

        return response()->json([
            'status' => $transaction->status,
            'vip_until' => optional(auth()->user()->fresh())->vip_until?->toDateTimeString(),
        ]);
    }

    /** Callback notifikasi Midtrans (server-to-server, signature diverifikasi). */
    public function callback(Request $request)
    {
        $payload = $request->all();

        if (! app(MidtransService::class)->verifyNotification($payload)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transaction = Transaction::where('midtrans_order_id', $payload['order_id'] ?? '')->first();

        if (! $transaction) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // pastikan jumlah bayar sesuai yang kita tagih
        if (isset($payload['gross_amount'])
            && (float) $payload['gross_amount'] !== (float) $transaction->amount) {
            return response()->json(['message' => 'Amount mismatch'], 403);
        }

        $this->applyMidtransStatus($transaction, $payload);

        return response()->json(['message' => 'OK']);
    }

    /** Halaman return setelah pembayaran (fallback bila snap.js tidak dipakai). */
    public function done()
    {
        $returnUrl = session()->pull('payment_return_url', '/');

        return redirect($returnUrl);
    }

    /** Terapkan status dari payload Midtrans ke transaksi + aktifkan VIP bila sukses. */
    private function applyMidtransStatus(Transaction $transaction, array $payload): void
    {
        if ($transaction->status === Transaction::STATUS_SUCCESS) {
            return;
        }

        $midtransService = app(MidtransService::class);
        $status = $midtransService->mapStatus($payload['transaction_status'] ?? null);

        if ($status === Transaction::STATUS_SUCCESS) {
            $transaction->status = Transaction::STATUS_SUCCESS;
            $transaction->paid_at = now();

            if (! empty($payload['payment_type'])) {
                $transaction->payment_method = $payload['payment_type'];
            }

            $transaction->save();

            $user = User::find($transaction->users_id);
            if ($user) {
                // pakai durasi dari transaksi; kalau data lama kosong, pakai pengaturan admin
                $days = $transaction->duration_days ?: (int) SettingsService::get('vip_days', 30);
                $user->extendVip($days);
            }

            return;
        }

        if ($status !== Transaction::STATUS_PENDING) {
            $transaction->status = $status;

            if (! empty($payload['payment_type'])) {
                $transaction->payment_method = $payload['payment_type'];
            }

            $transaction->save();
        }
    }
}
