<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin';
    protected $fillable = ['username', 'password'];
    protected $hidden = ['password'];

    public function reviews()
    {
        return $this->hasMany(Review::class, 'admin_id');
    }
}