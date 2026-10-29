@extends('layouts.public')

@section('title', 'VIP - SCREENED')

@section('content')
<div class="vip-page py-4">

    <div class="text-center mb-4">
        <h1 class="h3 mb-2"><i class="bi bi-crown-fill" style="color: var(--accent);"></i> Jadi Member VIP</h1>
        <p class="text-body-secondary mb-0">
            Nonton semua film &amp; episode tanpa batas, dan profile VIP.
        </p>

        @auth
            @if($isVip)
                <p class="mt-3 mb-0">
                    <span class="badge vip-badge">
                        <i class="bi bi-crown-fill"></i> VIP aktif sampai {{ $vipUntil->translatedFormat('d F Y') }}
                    </span>
                </p>
                <p class="small mb-0 mt-2" style="color: var(--accent);">
                    <i class="bi bi-arrow-repeat"></i> Bayar lagi = masa aktifmu bertambah.
                </p>
            @endif
        @endauth
    </div>

    {{-- ============ KARTU HARGA VIP (per paket) ============ --}}
    <div class="row g-3 justify-content-center align-items-stretch">
        @foreach($packages as $package)
            <div class="col-12 col-sm-6 col-lg-4 vip-card-col">
                <div class="card shadow-sm h-100 text-center">
                    <div class="card-body d-flex flex-column p-4">

                        <h2 class="h5 mb-1">{{ $package->name }}</h2>

                        <div class="vip-price">Rp {{ number_format((float) $package->price, 0, ',', '.') }}</div>

                        <p class="text-body-secondary small mb-2">
                            / {{ $package->duration_days }} hari &middot; semua konten terbuka
                        </p>

                        @if($isVip)
                            <p class="small mb-3 vip-renew-note">
                                <i class="bi bi-arrow-repeat"></i>
                                Bertambah dari {{ $vipUntil->translatedFormat('d F Y') }}
                                menjadi {{ $vipUntil->copy()->addDays($package->duration_days)->translatedFormat('d F Y') }}
                            </p>
                        @endif

                        <ul class="list-unstyled small text-start mx-auto mb-4 vip-benefits">
                            <li class="mb-1"><i class="bi bi-check-circle-fill me-2"></i>Nonton semua film &amp; serial</li>
                            <li class="mb-1"><i class="bi bi-check-circle-fill me-2"></i>Semua episode terbuka</li>
                            <li class="mb-1"><i class="bi bi-check-circle-fill me-2"></i>Tanpa batas waktu nonton</li>
                            <li><i class="bi bi-check-circle-fill me-2"></i>Berlaku {{ $package->duration_days }} hari</li>
                        </ul>

                        <div class="mt-auto">
                            <div class="alert alert-danger py-2 small d-none js-vip-error"></div>
                            <div class="alert alert-info py-2 small d-none js-vip-info"></div>

                            @guest
                                <a href="{{ route('login') }}" class="btn btn-gold w-100">
                                    <i class="bi bi-box-arrow-in-right"></i> Login dulu untuk Bayar
                                </a>
                            @else
                                <button type="button" class="btn btn-gold w-100 js-buy"
                                    data-package="{{ $package->id }}">
                                    <i class="bi bi-wallet2"></i>
                                    {{ $isVip ? 'Bayar Perpanjangan' : 'Bayar Sekarang' }}
                                </button>
                            @endguest
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <p class="text-center small text-body-secondary mt-3 mb-0">
        <i class="bi bi-shield-check"></i> Pembayaran aman via Midtrans &middot; VA, QRIS, e-wallet
    </p>

    <p class="text-center small mt-2 mb-0">
        <a href="{{ route('collection') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Kembali ke Collection
        </a>
    </p>

</div>
@endsection

@push('scripts')
    @auth
        @php($snapJs = app(\App\Services\MidtransService::class)->snapJsUrl())
        @php($clientKey = config('services.midtrans.client_key'))
        @if($clientKey)
            <script src="{{ $snapJs }}" data-client-key="{{ $clientKey }}"></script>
        @endif
    @endauth

    <script>
        (function () {
            var buttons = document.querySelectorAll('.js-buy');
            if (!buttons.length) return;

            var pollTries = 0;
            var activeBox = null;

            function showMsg(el, text, isError) {
                var box = el.querySelector(isError ? '.js-vip-error' : '.js-vip-info');
                var other = el.querySelector(isError ? '.js-vip-info' : '.js-vip-error');
                if (other) other.classList.add('d-none');
                box.textContent = text;
                box.className = 'alert py-2 small ' + (isError ? 'alert-danger' : 'alert-info');
                box.classList.remove('d-none');
            }

            function pollOrder(orderId) {
                pollTries = 0;
                showMsg(activeBox, 'Memeriksa status pembayaran...', false);

                var tick = function () {
                    fetch('{{ url('/payment/status') }}/' + encodeURIComponent(orderId), {
                        headers: { 'Accept': 'application/json' }
                    }).then(function (r) { return r.json(); }).then(function (d) {
                        if (d.status === 'success') {
                            showMsg(activeBox, 'Pembayaran berhasil! VIP aktif. Memuat ulang...', false);
                            setTimeout(function () { location.reload(); }, 800);
                            return;
                        }

                        if (d.status === 'failed' || d.status === 'expired') {
                            showMsg(activeBox, 'Pembayaran gagal atau berakhir. Silakan coba lagi.', true);
                            return;
                        }

                        if (++pollTries < 15) {
                            setTimeout(tick, 2000);
                        } else {
                            showMsg(activeBox, 'Pembayaran belum terkonfirmasi. Kalau kamu sudah bayar, tunggu sebentar lalu muat ulang halaman.', false);
                        }
                    }).catch(function () {
                        if (++pollTries < 15) setTimeout(tick, 3000);
                    });
                };

                tick();
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var card = btn.closest('.card');
                    var original = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                    card.querySelectorAll('.js-vip-error, .js-vip-info').forEach(function (b) { b.classList.add('d-none'); });
                    activeBox = card;

                    var payload = { return_url: location.href };
                    if (btn.dataset.package) payload.package_id = btn.dataset.package;

                    fetch('{{ route('checkout') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                    .then(function (res) {
                        if (!res.ok) throw new Error(res.data.message || 'Gagal memulai pembayaran.');

                        if (window.snap) {
                            snap.pay(res.data.token, {
                                onSuccess: function () { pollOrder(res.data.order_id); },
                                onPending: function () { pollOrder(res.data.order_id); },
                                onClose: function () { pollOrder(res.data.order_id); },
                                onError: function () {
                                    showMsg(activeBox, 'Terjadi kesalahan saat pembayaran. Coba lagi.', true);
                                }
                            });
                        } else {
                            location.href = res.data.redirect_url;
                        }
                    })
                    .catch(function (e) {
                        showMsg(activeBox, e.message || 'Gagal memulai pembayaran.', true);
                    })
                    .finally(function () {
                        btn.disabled = false;
                        btn.innerHTML = original;
                    });
                });
            });
        })();
    </script>
@endpush
