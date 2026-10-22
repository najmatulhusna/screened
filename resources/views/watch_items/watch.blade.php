@extends('layouts.public')

@section('title', 'Nonton ' . $watchItem->title . ($episode ? ' Ep ' . $episode : '') . ' - SCREENED')

@section('content')
<div class="container-fluid watch-page">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <a href="{{ route('watchitem.detail', $watchItem->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Detail
        </a>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($isVip)
                <span class="badge vip-badge">
                    <i class="bi bi-crown-fill"></i> VIP
                    @if(auth()->user()->vip_until)
                        &middot; s/d {{ auth()->user()->vip_until->format('d M Y') }}
                    @endif
                </span>
            @elseif(!$hasFullAccess)
                <span class="badge text-bg-secondary">
                    <i class="bi bi-person-lock"></i> Mode Non-VIP
                </span>
            @endif

            @if($isMovie)
                <span class="badge text-bg-dark"><i class="bi bi-camera-reels"></i> Film</span>
            @else
                <span class="badge text-bg-dark">
                    <i class="bi bi-collection-play"></i> Episode {{ $episode }}@if($watchItem->total_episodes)/{{ $watchItem->total_episodes }}@endif
                </span>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-4 watch-player-card">
        <div class="card-body p-2 p-md-3">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                <h1 class="h5 mb-0 text-truncate" style="max-width: 100%;">
                    {{ $watchItem->title }}
                    @if(!$isMovie) <span class="text-body-secondary fw-normal">- Ep {{ $episode }}</span> @endif
                </h1>

                @if($access === 'preview' && $previewRemaining !== null)
                    <span class="badge text-bg-warning" id="previewBadge">
                        <i class="bi bi-stopwatch"></i> Preview
                        <span class="preview-time"></span>
                    </span>
                @endif
            </div>

            <div class="watch-player-frame ratio ratio-16x9">
                @if($streamMissing)
                    <div class="watch-missing d-flex flex-column align-items-center justify-content-center text-body-secondary">
                        <i class="bi bi-emoji-frown fs-1 mb-2"></i>
                        <p class="mb-1 fw-semibold">Video belum tersedia</p>
                        <p class="small mb-0">Link streaming untuk {{ $isMovie ? 'film ini' : 'episode ini' }} belum diatur admin.</p>
                    </div>
                @elseif($access === 'locked')
                    <div class="watch-missing d-flex flex-column align-items-center justify-content-center text-body-secondary">
                        <i class="bi bi-lock-fill fs-1 mb-2"></i>
                        <p class="mb-3 fw-semibold">Konten terkunci</p>
                        @if(!$isMovie)
                            <a href="{{ route('watch', ['id' => $watchItem->id, 'ep' => 1]) }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-play-circle"></i> Tonton Episode 1
                            </a>
                        @else
                            <a href="{{ route('collection') }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-film"></i> Pilih Film Lain
                            </a>
                        @endif
                    </div>
                @else
                    <iframe id="playerFrame" src="{{ $streamUrl }}"
                        allowfullscreen
                        allow="autoplay; fullscreen; encrypted-media"
                        referrerpolicy="origin"
                        title="Player {{ $watchItem->title }}"></iframe>
                @endif
            </div>

            @if(!$hasFullAccess && !$streamMissing && $access !== 'locked')
                <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 gap-2">
                    <span class="small text-body-secondary">
                        @if($access === 'preview')
                            Tonton gratis 10 menit pertama, lalu lanjut dengan VIP.
                        @else
                            Episode 1 gratis &middot; Episode berikutnya butuh VIP.
                        @endif
                    </span>
                    <button type="button" class="btn btn-gold btn-sm" data-open-paywall>
                        <i class="bi bi-crown"></i> Jadi VIP
                    </button>
                </div>
            @endif

        </div>
    </div>

    @if(!$isMovie && count($episodes) > 1)
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2">
                <h3 class="card-title h6 mb-0"><i class="bi bi-collection-play"></i> Daftar Episode</h3>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($episodes as $epNumber => $info)
                        @if($info['current'])
                            <span class="btn btn-primary btn-sm">
                                <i class="bi bi-play-fill"></i> Ep {{ $epNumber }}
                            </span>
                        @elseif($info['locked'])
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-episode-locked>
                                <i class="bi bi-lock-fill"></i> Ep {{ $epNumber }}
                            </button>
                        @elseif(!$info['url'])
                            <span class="btn btn-outline-secondary btn-sm disabled" title="Link belum tersedia">
                                Ep {{ $epNumber }} <span class="small">(—)</span>
                            </span>
                        @else
                            <a href="{{ route('watch', ['id' => $watchItem->id, 'ep' => $epNumber]) }}"
                                class="btn btn-outline-primary btn-sm">Ep {{ $epNumber }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>

{{-- ============ MODAL PAYWALL ============ --}}
<div class="modal fade" id="paywallModal" tabindex="-1" aria-labelledby="paywallLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content paywall-content">
            <div class="modal-body text-center p-4">

                <div class="paywall-crown mb-3">
                    <i class="bi bi-crown-fill"></i>
                </div>

                <h2 class="h5 fw-bold mb-1" id="paywallLabel">Jadi VIP SCREENED</h2>
                <p class="text-body-secondary small mb-3">
                    Konten ini butuh VIP untuk {{ $access === 'preview' || $isMovie ? 'menonton tanpa batas' : 'melanjutkan ke episode berikutnya' }}.
                </p>

                <div class="paywall-price mb-1">
                    Rp {{ number_format((float) \App\Services\SettingsService::get('vip_price', 30000), 0, ',', '.') }}
                </div>
                <p class="text-body-secondary small mb-3">
                    / {{ (int) \App\Services\SettingsService::get('vip_days', 30) }} hari &middot; semua konten terbuka
                </p>

                <ul class="list-unstyled small text-start mx-auto mb-3" style="max-width: 280px;">
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Nonton semua film &amp; serial</li>
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Semua episode terbuka</li>
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Tanpa batas waktu nonton</li>
                </ul>

                <div id="paywallError" class="alert alert-danger py-2 small d-none"></div>
                <div id="paywallInfo" class="alert alert-info py-2 small d-none"></div>

                <button type="button" class="btn btn-gold w-100 mb-2" id="btnCheckout">
                    <i class="bi bi-wallet2"></i> Bayar Sekarang (Midtrans)
                </button>

                <button type="button" class="btn btn-outline-secondary w-100 mb-2 d-none" id="paywallCloseBtn" data-bs-dismiss="modal">
                    Nanti Dulu
                </button>

                <a href="{{ route('collection') }}" class="btn btn-link btn-sm text-body-secondary">
                    Pilih Konten Lain
                </a>

                <p class="small text-body-secondary mt-2 mb-0">
                    <i class="bi bi-shield-check"></i> Pembayaran aman via Midtrans &middot; VA, QRIS, e-wallet
                </p>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @if(!$hasFullAccess && !$streamMissing)
        @php($snapJs = app(\App\Services\MidtransService::class)->snapJsUrl())
        @php($clientKey = config('services.midtrans.client_key'))
        @if($clientKey)
            <script src="{{ $snapJs }}" data-client-key="{{ $clientKey }}"></script>
        @endif
    @endif

    <script>
        (function () {
            var WATCH = {
                access: @json($access),
                previewRemaining: @json($previewRemaining),
                autoOpen: @json($autoOpenPaywall),
                paywallNow: @json($paywallNow),
                dismissible: @json(in_array($access, ['episode1', 'locked'])),
                checkoutUrl: @json(route('checkout')),
                statusUrlBase: @json(url('/payment/status')),
                csrf: @json(csrf_token())
            };

            var paywallInstance = null;
            var pollTries = 0;

            function openPaywall(dismissible) {
                var el = document.getElementById('paywallModal');
                var closeBtn = document.getElementById('paywallCloseBtn');

                if (dismissible) {
                    closeBtn.classList.remove('d-none');
                } else {
                    closeBtn.classList.add('d-none');
                }

                var existing = bootstrap.Modal.getInstance(el);
                if (existing) existing.dispose();

                paywallInstance = bootstrap.Modal.getOrCreateInstance(el, {
                    backdrop: dismissible ? true : 'static',
                    keyboard: !!dismissible
                });
                paywallInstance.show();
            }

            // tombol "Jadi VIP" manual
            document.querySelectorAll('[data-open-paywall]').forEach(function (btn) {
                btn.addEventListener('click', function () { openPaywall(true); });
            });

            // episode terkunci diklik dari daftar
            document.querySelectorAll('[data-episode-locked]').forEach(function (btn) {
                btn.addEventListener('click', function () { openPaywall(true); });
            });

            // ---- countdown preview 10 menit (movie non-VIP) ----
            function expirePreview() {
                var frame = document.getElementById('playerFrame');
                if (frame) frame.src = 'about:blank';

                var badge = document.getElementById('previewBadge');
                if (badge) badge.classList.add('d-none');

                openPaywall(false);
            }

            if (WATCH.access === 'preview' && WATCH.previewRemaining !== null) {
                var remaining = WATCH.previewRemaining;
                var badge = document.getElementById('previewBadge');
                var label = badge ? badge.querySelector('.preview-time') : null;

                var tick = function () {
                    if (remaining <= 0) { expirePreview(); return; }

                    if (label) {
                        var m = Math.floor(remaining / 60);
                        var s = remaining % 60;
                        label.textContent = m + ':' + (s < 10 ? '0' : '') + s;
                    }

                    remaining--;
                    setTimeout(tick, 1000);
                };

                tick();
            }

            // ---- buka paywall otomatis ----
            if (WATCH.paywallNow && WATCH.access === 'locked') {
                openPaywall(WATCH.dismissible);
            }

            if (WATCH.autoOpen) {
                setTimeout(function () { openPaywall(WATCH.dismissible); }, 2000);
            }

            // ---- checkout Midtrans ----
            var btnCheckout = document.getElementById('btnCheckout');

            function showPaywallMessage(id, text, isError) {
                var box = document.getElementById(id);
                box.textContent = text;
                box.className = 'alert py-2 small ' + (isError ? 'alert-danger' : 'alert-info');
                box.classList.remove('d-none');
            }

            function pollOrder(orderId) {
                pollTries = 0;
                showPaywallMessage('paywallInfo', 'Memeriksa status pembayaran...', false);

                var tick = function () {
                    fetch(WATCH.statusUrlBase + '/' + encodeURIComponent(orderId), {
                        headers: { 'Accept': 'application/json' }
                    }).then(function (r) { return r.json(); }).then(function (d) {
                        if (d.status === 'success') {
                            showPaywallMessage('paywallInfo', 'Pembayaran berhasil! VIP aktif. Memuat ulang...', false);
                            setTimeout(function () { location.reload(); }, 800);
                            return;
                        }

                        if (d.status === 'failed' || d.status === 'expired') {
                            showPaywallMessage('paywallError', 'Pembayaran gagal atau berakhir. Silakan coba lagi.', true);
                            document.getElementById('paywallInfo').classList.add('d-none');
                            return;
                        }

                        if (++pollTries < 15) {
                            setTimeout(tick, 2000);
                        } else {
                            showPaywallMessage('paywallInfo', 'Pembayaran belum terkonfirmasi. Jika kamu sudah bayar, tunggu sebentar lalu muat ulang halaman.', false);
                        }
                    }).catch(function () {
                        if (++pollTries < 15) setTimeout(tick, 3000);
                    });
                };

                tick();
            }

            if (btnCheckout) {
                btnCheckout.addEventListener('click', function () {
                    var original = btnCheckout.innerHTML;
                    btnCheckout.disabled = true;
                    btnCheckout.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                    document.getElementById('paywallError').classList.add('d-none');

                    fetch(WATCH.checkoutUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': WATCH.csrf
                        },
                        body: JSON.stringify({ return_url: location.href })
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
                                    showPaywallMessage('paywallError', 'Terjadi kesalahan saat pembayaran. Coba lagi.', true);
                                }
                            });
                        } else {
                            // snap.js tidak termuat -> pakai halaman pembayaran Midtrans
                            location.href = res.data.redirect_url;
                        }
                    })
                    .catch(function (e) {
                        showPaywallMessage('paywallError', e.message || 'Gagal memulai pembayaran.', true);
                    })
                    .finally(function () {
                        btnCheckout.disabled = false;
                        btnCheckout.innerHTML = original;
                    });
                });
            }
        })();
    </script>
@endpush
