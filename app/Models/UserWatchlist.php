<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris = satu judul di My Album milik seorang user.
 * Dipakai untuk statistik pribadi & pengelolaan tontonan (fitur VIP).
 */
class UserWatchlist extends Model
{
    /** Semua status tontonan yang didukung My Album. */
    public const STATUSES = ['Plan to Watch', 'Watching', 'Completed', 'On Hold', 'Dropped'];

    /** Tipe yang didukung (sama dengan enum pada watch_items). */
    public const TYPES = ['Movie', 'Drama', 'Series', 'Tv Show'];

    protected $table = 'user_watchlists';

    protected $fillable = [
        'user_id',
        'watch_item_id',
        'title',
        'type',
        'release_year',
        'genre',
        'country',
        'director_creator',
        'cast_list',
        'synopsis',
        'poster',
        'duration',
        'total_episodes',
        'watch_status',
        'personal_rating',
        'date_started',
        'date_completed',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'release_year' => 'integer',
            'duration' => 'integer',
            'total_episodes' => 'integer',
            'personal_rating' => 'float',
            'date_started' => 'date',
            'date_completed' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Null untuk entri manual. */
    public function watchItem()
    {
        return $this->belongsTo(WatchItem::class, 'watch_item_id');
    }

    /** True bila judul ini berasal dari Collection (bukan input manual). */
    public function fromCollection(): bool
    {
        return $this->watch_item_id !== null;
    }

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
