{{--
    Modal upsell My Album untuk user non-VIP (Free maupun VIP yang sudah habis masa aktifnya).
    Dipakai di halaman My Album dan halaman detail Collection.
    Ikon memakai bi-lock-fill karena bootstrap-icons 1.13.1 tidak punya glyph crown.
--}}
<div class="modal fade" id="vipAlbumModal" tabindex="-1" aria-labelledby="vipAlbumModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content vip-album-modal">

            <div class="modal-body text-center p-4 p-md-5">
                <span class="vip-album-modal-icon"><i class="bi bi-lock-fill" aria-hidden="true"></i></span>

                <h2 class="h5 mt-3 mb-2" id="vipAlbumModalLabel">
                    @auth
                        {{ auth()->user()->vip_until ? 'Your VIP Has Expired' : 'Exclusive VIP Feature' }}
                    @else
                        Exclusive VIP Feature
                    @endauth
                </h2>

                <p class="text-body-secondary mb-4 mx-auto" style="max-width: 420px">
                    @auth
                        {{ auth()->user()->vip_until
                            ? 'VIP kamu sudah berakhir. Seluruh judul di My Album tetap tersimpan — perpanjang VIP untuk menambah dan mengedit lagi.'
                            : 'Unlock your personal watch collection with SCREENED VIP.' }}
                    @else
                        Login dan berlangganan SCREENED VIP untuk mulai membuat album tontonanmu.
                    @endauth
                </p>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('vip') }}" class="btn btn-gold">
                        <i class="bi bi-stars" aria-hidden="true"></i>
                        @auth
                            {{ auth()->user()->vip_until ? 'Renew VIP' : 'Upgrade to VIP' }}
                        @else
                            Explore VIP
                        @endauth
                    </a>

                    @auth
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Nanti dulu
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light">Login</a>
                    @endauth
                </div>
            </div>

        </div>
    </div>
</div>
