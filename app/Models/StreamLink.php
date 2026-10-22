<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StreamLink extends Model
{
    protected $table = 'stream_links';

    protected $fillable = [
        'watch_items_id',
        'episode_number',
        'url',
    ];

    public function watchItem()
    {
        return $this->belongsTo(WatchItem::class, 'watch_items_id');
    }
}
