<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_EXPIRED = 'expired';

    protected $table = 'transactions';

    protected $fillable = [
        'users_id',
        'type',
        'amount',
        'status',
        'payment_method',
        'midtrans_order_id',
        'snap_token',
        'paid_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }
}
