<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien Midtrans Snap (tanpa package tambahan, pakai HTTP facade).
 *
 * .env:
 *   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
 *   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
 *   MIDTRANS_IS_PRODUCTION=false
 */
class MidtransService
{
    public function serverKey(): string
    {
        $key = config('services.midtrans.server_key');

        if (! $key) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diisi di .env');
        }

        return $key;
    }

    public function clientKey(): string
    {
        return (string) config('services.midtrans.client_key');
    }

    public function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production');
    }

    public function baseUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    public function snapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function redirectUrl(string $snapToken): string
    {
        $host = $this->isProduction()
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        return $host.'/snap/v2/vtweb/'.$snapToken;
    }

    /** Buat sesi Snap -> ['token' => ..., 'redirect_url' => ...] */
    public function createSnapTransaction(string $orderId, float $grossAmount, array $items, array $customer): array
    {
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($grossAmount),
            ],
            'item_details' => $items,
            'customer_details' => $customer,
            'expiry' => [
                'unit' => 'minutes',
                'duration' => 30,
            ],
        ];

        // Endpoint Snap Midtrans: /snap/v1/transactions (bukan /v1/transactions -> 404)
        $response = Http::withBasicAuth($this->serverKey(), '')
            ->timeout(30)
            ->post($this->baseUrl().'/snap/v1/transactions', $payload);

        if (! $response->successful()) {
            report(new RuntimeException(
                'Midtrans Snap error: '.$response->status().' '.$response->body()
            ));

            throw new RuntimeException('Gagal membuat sesi pembayaran Midtrans. Coba lagi nanti.');
        }

        return [
            'token' => $response->json('token'),
            'redirect_url' => $response->json('redirect_url'),
        ];
    }

    /** Cek status transaksi langsung ke API Midtrans (dipakai polling saat callback tidak masuk). */
    public function getStatus(string $orderId): ?array
    {
        $response = Http::withBasicAuth($this->serverKey(), '')
            ->timeout(30)
            ->get($this->baseUrl().'/v2/'.urlencode($orderId).'/status');

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Verifikasi signature_key notifikasi callback:
     * sha512(order_id + status_code + gross_amount + server_key)
     * Gagal-aman: tanpa server key di .env, semua notifikasi ditolak.
     */
    public function verifyNotification(array $payload): bool
    {
        $key = config('services.midtrans.server_key');

        if (!$key) {
            return false;
        }

        $expected = hash(
            'sha512',
            ($payload['order_id'] ?? '')
            . ($payload['status_code'] ?? '')
            . ($payload['gross_amount'] ?? '')
            . $key
        );

        return isset($payload['signature_key'])
            && hash_equals($expected, (string) $payload['signature_key']);
    }

    /**
     * Petakan status Midtrans ke status internal transaksi.
     * capture/settlement = sukses, deny/cancel/expire = gagal.
     */
    public function mapStatus(?string $midtransStatus): string
    {
        switch ($midtransStatus) {
            case 'capture':
            case 'settlement':
                return Transaction::STATUS_SUCCESS;
            case 'deny':
            case 'cancel':
            case 'expire':
                return Transaction::STATUS_EXPIRED;
            default:
                return Transaction::STATUS_PENDING;
        }
    }
}
