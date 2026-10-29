{{--
    Stream Links (per episode) — box input + JS render dalam satu file.
    Dipakai halaman Add, Edit, dan modal Add/Edit di halaman Manage.
    Box: <div data-stream-links data-existing='{"1":"https://..."}'>
    JS : renderStreamLinks(box) merender baris Ep 1..N (Movie = 1 baris "URL Film").
--}}
@php
    // saat validasi gagal, tampilkan kembali nilai yang dikirim admin
    $slExisting = $existing ?? [];
    if (old('stream_links')) {
        $slExisting = [];
        foreach (old('stream_links') as $row) {
            if (isset($row['episode_number'])) {
                $slExisting[(int) $row['episode_number']] = $row['url'] ?? '';
            }
        }
    }
@endphp
<div class="stream-links-box"
    data-stream-links
    data-existing='@json($slExisting)'></div>
<script>
    (function () {
        /**
         * Render baris input stream link:
         * - Movie  -> 1 baris "URL Film" (episode_number = 0)
         * - Serial -> baris Ep 1..N sesuai kolom Total Episodes
         * Nilai lama (data-existing) dan yang sudah diketik di DOM dipertahankan.
         */
        window.renderStreamLinks = function (box) {
            if (!box) return;

            var form = box.closest('form');
            var typeSel = form ? form.querySelector('select[name="type"]') : null;
            var epInput = form ? form.querySelector('input[name="total_episodes"]') : null;

            var type = typeSel ? typeSel.value : 'Movie';
            var isMovie = type === 'Movie';
            var count = isMovie ? 1 : Math.max(parseInt(epInput && epInput.value, 10) || 1, 1);

            var map = {};
            try { map = JSON.parse(box.getAttribute('data-existing') || '{}') || {}; } catch (e) { map = {}; }

            // pertahankan URL yang sudah diketik di form ini
            box.querySelectorAll('.stream-link-row').forEach(function (row) {
                var ep = row.querySelector('[data-sl-ep]');
                var url = row.querySelector('[data-sl-url]');
                if (ep && url) map[String(ep.value)] = url.value;
            });

            box.innerHTML = '';

            for (var i = 0; i < count; i++) {
                var epNum = isMovie ? 0 : i + 1;
                var label = isMovie ? 'URL Film' : 'Ep ' + epNum;
                var val = (map[String(epNum)] !== undefined) ? map[String(epNum)] : (map[epNum] || '');

                var wrap = document.createElement('div');
                wrap.className = 'input-group mb-2 stream-link-row';

                var lbl = document.createElement('span');
                lbl.className = 'input-group-text sl-label';
                lbl.textContent = label;

                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'stream_links[' + i + '][episode_number]';
                hidden.value = epNum;
                hidden.setAttribute('data-sl-ep', '');

                var input = document.createElement('input');
                input.type = 'url';
                input.className = 'form-control';
                input.name = 'stream_links[' + i + '][url]';
                input.placeholder = 'https://streamtape.com/e/... atau link video lain';
                input.value = val;
                input.setAttribute('data-sl-url', '');

                wrap.appendChild(lbl);
                wrap.appendChild(hidden);
                wrap.appendChild(input);
                box.appendChild(wrap);
            }
        };

        function wire(box) {
            var form = box.closest('form');
            if (!form || form.dataset.slWired === '1') return;
            form.dataset.slWired = '1';

            var typeSel = form.querySelector('select[name="type"]');
            var epInput = form.querySelector('input[name="total_episodes"]');

            if (typeSel) {
                typeSel.addEventListener('change', function () { window.renderStreamLinks(box); });
            }
            if (epInput) {
                epInput.addEventListener('input', function () { window.renderStreamLinks(box); });
            }

            window.renderStreamLinks(box);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-stream-links]').forEach(wire);
        });
    })();
</script>
