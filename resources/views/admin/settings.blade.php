@extends('layouts.adminlte')

@section('title', 'Pengaturan - SCREENED')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Pengaturan</h1>
    </div>

    <div class="row g-4">

        <div class="col-lg-6">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-crown"></i> Paket VIP</h3>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label" for="vip_price">Harga VIP (Rp)</label>
                                <input type="number" name="vip_price" id="vip_price" class="form-control @error('vip_price') is-invalid @enderror"
                                    value="{{ old('vip_price', $settings['vip_price']) }}" min="1000" step="1000" required>
                                @error('vip_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Harga yang ditagih ke user untuk 1x pembayaran.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="vip_days">Durasi VIP (hari)</label>
                                <input type="number" name="vip_days" id="vip_days" class="form-control @error('vip_days') is-invalid @enderror"
                                    value="{{ old('vip_days', $settings['vip_days']) }}" min="1" max="3650" required>
                                @error('vip_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Masa aktif VIP setelah pembayaran berhasil. Perpanjangan menumpuk dari expiry lama.</div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-credit-card"></i> Status Payment Gateway</h3>
                </div>

                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi {{ $midtransConfigured ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i>
                            Midtrans Server Key
                            <span class="text-body-secondary small">({{ $midtransConfigured ? 'sudah diisi di .env' : 'BELUM diisi di .env (MIDTRANS_SERVER_KEY)' }})</span>
                        </li>
                        <li class="mb-2">
                            <i class="bi {{ $midtransConfigured ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i>
                            Midtrans Client Key
                            <span class="text-body-secondary small">({{ config('services.midtrans.client_key') ? 'sudah diisi' : 'BELUM diisi (MIDTRANS_CLIENT_KEY)' }})</span>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-globe me-2 text-body-secondary"></i>
                            Environment:
                            <span class="badge {{ $midtransProduction ? 'text-bg-danger' : 'text-bg-info' }}">
                                {{ $midtransProduction ? 'Production' : 'Sandbox' }}
                            </span>
                        </li>
                    </ul>

                    <hr>

                    <p class="small text-body-secondary mb-0">
                        <i class="bi bi-info-circle"></i>
                        Ubah key/lingkungan di file <code>.env</code> (<code>MIDTRANS_SERVER_KEY</code>,
                        <code>MIDTRANS_CLIENT_KEY</code>, <code>MIDTRANS_IS_PRODUCTION</code>) lalu jalankan
                        <code>php artisan config:clear</code>. Notifikasi callback:
                        <code>POST /payment/callback</code>.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-clock-history"></i> Riwayat Singkat</h3>
                </div>
                <div class="card-body">
                    <p class="small text-body-secondary mb-0">
                        Harga &amp; durasi disimpan di <code>storage/app/settings.json</code> sehingga tidak
                        memerlukan migration. Perubahan berlaku langsung untuk checkout berikutnya.
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
