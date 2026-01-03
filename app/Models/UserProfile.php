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
        'professional_title',   // new
        'city',                 // new
        'bio',                  // new
        'hourly_rate',           // new
        'is_profile_complete',  // new
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
