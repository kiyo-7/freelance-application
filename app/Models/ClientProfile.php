<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'phone_number',
        'location',
        'avatar_url',
    ];

    // numeric auto-increment primary key
    // no HasUuids, no $incrementing=false, no $keyType='string'

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
