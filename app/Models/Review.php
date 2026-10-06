<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'watch_items_id',
        'users_id',
        'admin_id',
        'episode_watched',
        'status',
        'rating',
        'review_text',
    ];

    public function watchItem()
    {
        return $this->belongsTo(WatchItem::class, 'watch_items_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}