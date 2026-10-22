<?php

namespace App\Services;

/**
 * Pengaturan site (harga & durasi VIP) disimpan di storage/app/settings.json
 * agar bisa diubah dari admin panel tanpa migration.
 */
class SettingsService
{
    private const DEFAULTS = [
        'vip_price' => 30000,
        'vip_days' => 30,
    ];

    public static function path(): string
    {
        return storage_path('app/settings.json');
    }

    public static function all(): array
    {
        $stored = [];

        if (is_file(self::path())) {
            $decoded = json_decode((string) file_get_contents(self::path()), true);
            if (is_array($decoded)) {
                $stored = $decoded;
            }
        }

        return array_merge(self::DEFAULTS, $stored);
    }

    public static function get(string $key, $default = null)
    {
        $all = self::all();

        return $all[$key] ?? $default;
    }

    public static function save(array $data): void
    {
        $merged = array_merge(self::all(), $data);

        if (! is_dir(dirname(self::path()))) {
            mkdir(dirname(self::path()), 0755, true);
        }

        file_put_contents(
            self::path(),
            json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
