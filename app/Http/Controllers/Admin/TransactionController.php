<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VipPackage;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Halaman kelola VIP: tambah/hapus paket VIP + data berlangganan.
     * Tabel transaksinya diisi lewat AJAX (DataTables).
     */
    public function index()
    {
        $stats = [
            'success_amount' => (float) Transaction::where('status', Transaction::STATUS_SUCCESS)->sum('amount'),
            'success_count' => Transaction::where('status', Transaction::STATUS_SUCCESS)->count(),
            'pending_count' => Transaction::where('status', Transaction::STATUS_PENDING)->count(),
            'total_count' => Transaction::count(),
        ];

        return view('admin.transactions', [
            'stats' => $stats,
            'packages' => VipPackage::latest()->get(),
            'users' => User::orderBy('username')->get(['id', 'username', 'email']),
        ]);
    }

    /** Data transaksi untuk DataTables (dipanggil via AJAX). */
    public function apiIndex(Request $request)
    {
        $query = Transaction::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rows = $query->get()->map(function (Transaction $transaction) {
            $user = $transaction->user;

            return [
                'id' => $transaction->id,
                'username' => $user?->username ?? '-',
                'email' => $user?->email ?? '-',
                'vip_until' => $user?->isVip() ? $user->vip_until->format('d M Y H:i') : null,
                'type' => strtoupper($transaction->type),
                'amount' => (float) $transaction->amount,
                'duration_days' => $transaction->duration_days,
                'status' => $transaction->status,
                'payment_method' => $transaction->payment_method ?: '-',
                'midtrans_order_id' => $transaction->midtrans_order_id ?: '-',
                'created_at' => $transaction->created_at?->format('d M Y H:i'),
                'paid_at' => $transaction->paid_at?->format('d M Y H:i'),
                'action' => '<form method="POST" action="' . route('admin.transactions.destroy', $transaction->id) . '">'
                    . csrf_field() . method_field('DELETE')
                    . '<button type="submit" class="btn btn-sm btn-outline-danger"'
                    . ' data-confirm'
                    . ' data-title="Hapus transaksi ini?"'
                    . ' data-text="Transaksi ' . e($transaction->midtrans_order_id) . ' akan dihapus permanen."'
                    . ' data-submit="Ya, Hapus">'
                    . '<i class="bi bi-trash"></i> Hapus</button>'
                    . '</form>',
            ];
        });

        return response()->json(['data' => $rows->values()]);
    }

    /** Tambah transaksi manual (pembayaran offline / koreksi data). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'users_id' => 'required|integer|exists:users,id',
            'amount' => 'required|integer|min:1|max:1000000000',
            'duration_days' => 'required|integer|min:1|max:3650',
            'status' => 'required|in:pending,success,failed,expired',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $transaction = Transaction::create([
            'users_id' => $data['users_id'],
            'type' => 'vip',
            'amount' => $data['amount'],
            'duration_days' => $data['duration_days'],
            'status' => $data['status'],
            'payment_method' => $data['payment_method'] ?: 'Manual',
            'midtrans_order_id' => 'MANUAL-' . now()->format('YmdHis') . '-' . strtoupper(substr(uniqid(), -4)),
            'paid_at' => $data['status'] === Transaction::STATUS_SUCCESS ? now() : null,
        ]);

        // transaksi sukses langsung menambah masa aktif VIP user
        if ($transaction->status === Transaction::STATUS_SUCCESS) {
            User::find($transaction->users_id)?->extendVip($transaction->duration_days);
        }

        return back()->with(
            'success',
            'Transaksi manual ditambahkan (' . $transaction->midtrans_order_id . ') — '
            . 'Rp ' . number_format($data['amount'], 0, ',', '.') . ' untuk ' . $data['duration_days'] . ' hari.'
        );
    }

    /** Hapus satu baris transaksi dari dashboard / halaman transaksi. */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return back()->with('success', 'Transaksi ' . $transaction->midtrans_order_id . ' dihapus.');
    }
}
