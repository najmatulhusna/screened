<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'data',
        'vip_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'vip_until' => 'datetime',
        ];
    }
    
    public function reviews()
    {
        return $this->hasMany(Review::class, 'users_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'users_id');
    }

    /** Status VIP aktif (masa berlaku belum lewat). */
    public function isVip(): bool
    {
        return $this->vip_until !== null && $this->vip_until->isFuture();
    }

    /** Tambah masa aktif VIP dari sekarang atau dari expiry lama jika masih berlaku. */
    public function extendVip(int $days): void
    {
        $base = $this->vip_until && $this->vip_until->isFuture()
            ? $this->vip_until->copy()
            : now();

        $this->vip_until = $base->addDays($days);
        $this->save();
    }

}
