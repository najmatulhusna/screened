<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('midtrans_order_id', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%'));
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        $stats = [
            'success_amount' => (float) Transaction::where('status', Transaction::STATUS_SUCCESS)->sum('amount'),
            'success_count' => Transaction::where('status', Transaction::STATUS_SUCCESS)->count(),
            'pending_count' => Transaction::where('status', Transaction::STATUS_PENDING)->count(),
            'total_count' => Transaction::count(),
        ];

        return view('admin.transactions', compact('transactions', 'stats'));
    }
}
