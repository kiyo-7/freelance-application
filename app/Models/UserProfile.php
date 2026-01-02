<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'profile_image',
        'skills',
        'portfolio',
        'rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
