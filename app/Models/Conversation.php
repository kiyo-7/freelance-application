<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'freelancer_id',
        'last_message',
        'last_message_time',
        'client_unread',
        'freelancer_unread',
    ];

    protected $casts = [
        'last_message_time' => 'datetime',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('timestamp', 'desc');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany('timestamp');
    }
}
