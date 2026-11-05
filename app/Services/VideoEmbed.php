<?php

namespace App\Services;

/**
 * Konversi URL video tempel-an admin ke bentuk embed yang boleh dimuat di iframe,
 * plus deteksi host yang menolak embedding (t.me, facebook, dst) supaya player
 * tidak tampil kosong — link dibuka di tab baru sebagai gantinya.
 */
class VideoEmbed
{
    /** Host yang diketahui menolak iframe (X-Frame-Options / CSP frame-ancestors). */
    private const BLOCKED_HOSTS = [
        't.me', 'telegram.me', 'telegram.dog',
        'facebook.com', 'fb.watch',
        'instagram.com',
        'twitter.com', 'x.com',
        'tiktok.com', 'vm.tiktok.com',
        'open.spotify.com', 'spotify.com',
        'soundcloud.com',
    ];

    /**
     * Kembalikan URL siap-iframe. YouTube/Streamtape/Drive/Vimeo/Dailymotion
     * dikonversi ke endpoint embed-nya; host lain dikembalikan apa adanya;
     * URL kosong -> null.
     */
    public static function toEmbed(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        // YouTube: youtu.be/ID, watch?v=ID, shorts/ID, live/ID, embed/ID
        if (preg_match('~youtu\.be/([A-Za-z0-9_-]+)~i', $url, $m)
            || preg_match('~youtube\.com/(?:watch\?(?:[^"&]*&)?v=|shorts/|live/|embed/)([A-Za-z0-9_-]+)~i', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // Streamtape: /e/ID, /v/ID, /embed/ID, /get_video?id=ID -> selalu pakai /e/ (endpoint embed resmi)
        if (preg_match('#(?:streamtape\.(?:com|to|net|eu|org)|scloud\.to)/(?:e|v|embed)/([A-Za-z0-9]+)#i', $url, $m)
            || preg_match('#(?:streamtape\.(?:com|to|net|eu|org)|scloud\.to)/get_video\?id=([A-Za-z0-9]+)#i', $url, $m)) {
            return 'https://streamtape.com/e/' . $m[1];
        }

        // Google Drive: /file/d/ID/... atau open?id=ID -> halaman preview (satu-satunya yang boleh di-iframe)
        if (preg_match('#drive\.google\.com/file/d/([A-Za-z0-9_-]+)#i', $url, $m)
            || preg_match('#drive\.google\.com/open\?id=([A-Za-z0-9_-]+)#i', $url, $m)) {
            return 'https://drive.google.com/file/d/' . $m[1] . '/preview';
        }

        // Vimeo: vimeo.com/ID atau vimeo.com/video/ID -> player resmi
        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#i', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        // Dailymotion: dailymotion.com/video/ID atau dai.ly/ID
        if (preg_match('#dailymotion\.com/video/([A-Za-z0-9]+)#i', $url, $m)
            || preg_match('#dai\.ly/([A-Za-z0-9]+)#i', $url, $m)) {
            return 'https://www.dailymotion.com/embed/video/' . $m[1];
        }

        return $url;
    }

    /**
     * True kalau URL aman dimuat langsung di iframe:
     * harus http/https dan hostnya tidak menolak embedding.
     */
    public static function isEmbeddable(?string $url): bool
    {
        $url = trim((string) $url);
        if (! preg_match('#^https?://#i', $url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return false;
        }

        $host = strtolower(preg_replace('/^www\./', '', $host));

        foreach (self::BLOCKED_HOSTS as $blocked) {
            $blocked = preg_replace('/^www\./', '', $blocked);
            if ($host === $blocked || str_ends_with($host, '.' . $blocked)) {
                return false;
            }
        }

        return true;
    }

    /** True kalau URL masih boleh dibuka di tab baru (minimal http/https). */
    public static function isOpenable(?string $url): bool
    {
        return (bool) preg_match('#^https?://#i', trim((string) $url));
    }
}
