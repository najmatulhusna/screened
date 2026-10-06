<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Hapus?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal"
                    aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="confirmModalText">Tindakan ini tidak bisa dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmModalYes">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var pendingForm = null;
    var el = document.getElementById('confirmModal');
    if (!el) return;

    var titleEl = document.getElementById('confirmModalTitle');
    var textEl = document.getElementById('confirmModalText');
    var yesEl = document.getElementById('confirmModalYes');

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-confirm]');
        if (!btn) return;

        e.preventDefault();
        pendingForm = btn.form;

        titleEl.textContent = btn.dataset.title || 'Hapus?';
        textEl.textContent = btn.dataset.text || 'Tindakan ini tidak bisa dibatalkan.';
        yesEl.textContent = btn.dataset.submit || 'Ya, Hapus';

        // fallback kalau Bootstrap Modal tidak tersedia
        if (!window.bootstrap || !bootstrap.Modal) {
            if (confirm(titleEl.textContent + '\n' + textEl.textContent)) {
                if (pendingForm) pendingForm.submit();
            }
            return;
        }

        bootstrap.Modal.getOrCreateInstance(el).show();
    });

    yesEl.addEventListener('click', function () {
        if (pendingForm) {
            pendingForm.submit();
        }
    });
})();
</script>
@endpush
