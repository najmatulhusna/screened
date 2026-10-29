<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Paket VIP: harga + berlaku berapa hari.
 * Dikelola admin di halaman Transaksi (tambah / hapus).
 */
class VipPackage extends Model
{
    protected $table = 'vip_packages';

    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'duration_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
