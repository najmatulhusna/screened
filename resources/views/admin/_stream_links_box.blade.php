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
