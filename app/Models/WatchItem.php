<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchItem extends Model
{
    protected $fillable = [
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
    ];

    public function reviews() { 
        return $this->hasMany(Review::class, 'watch_items_id'); }

    public function adminReviews() {
        return $this->reviews()->whereNotNull('admin_id');}

    public function userReviews() {
        return $this->reviews()->whereNotNull('users_id');}

    public function getAllReviews() {
        return $this->reviews()->with('user', 'admin')->latest()->get();}

    public function getReviewCount(): int {
        return $this->reviews()->count(); }

    public function getAvgUserRatingAttribute(): ?float {
        $avg = $this->reviews()->whereNotNull('rating')->avg('rating');
        return $avg ? round($avg, 1) : null; }
}
