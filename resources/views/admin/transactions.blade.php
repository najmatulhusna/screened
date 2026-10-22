@extends('layouts.adminlte')

@section('title', 'Transaksi - SCREENED')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Transaksi</h1>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="bi bi-cash-stack"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendapatan VIP</span>
                    <span class="info-box-number">Rp {{ number_format($stats['success_amount'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="bi bi-check2-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Berhasil</span>
                    <span class="info-box-number">{{ $stats['success_count'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="bi bi-hourglass-split"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ $stats['pending_count'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="bi bi-receipt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Transaksi</span>
                    <span class="info-box-number">{{ $stats['total_count'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-list-ul"></i> Daftar Transaksi</h3>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari order ID / username / email"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach(['pending', 'success', 'failed', 'expired'] as $statusOption)
                            <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>
                                {{ ucfirst($statusOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
                    <a href="{{ route('admin.transactions') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Metode</th>
                            <th>Order ID</th>
                            <th>Dibayar</th>
                            <th>Berlaku s/d</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->id }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $transaction->user?->username ?? '-' }}</span>
                                    <br><small class="text-body-secondary">{{ $transaction->user?->email }}</small>
                                </td>
                                <td><span class="badge text-bg-dark">{{ strtoupper($transaction->type) }}</span></td>
                                <td>Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</td>
                                <td>
                                    @php
                                        $statusBadge = [
                                            'success' => 'text-bg-success',
                                            'pending' => 'text-bg-warning',
                                            'failed'  => 'text-bg-danger',
                                            'expired' => 'text-bg-secondary',
                                        ][$transaction->status] ?? 'text-bg-secondary';
                                    @endphp
                                    <span class="badge {{ $statusBadge }}">{{ $transaction->status }}</span>
                                </td>
                                <td>{{ $transaction->payment_method ?: '-' }}</td>
                                <td><code class="small">{{ $transaction->midtrans_order_id }}</code></td>
                                <td>{{ $transaction->paid_at?->format('d M Y H:i') ?: '-' }}</td>
                                <td>
                                    @if($transaction->user?->isVip())
                                        <span class="badge vip-badge">VIP</span>
                                        <br><small>{{ $transaction->user->vip_until?->format('d M Y H:i') }}</small>
                                    @else
                                        <small class="text-body-secondary">-</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-body-secondary py-4">Belum ada transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transactions->links() }}

        </div>
    </div>

</div>
@endsection
