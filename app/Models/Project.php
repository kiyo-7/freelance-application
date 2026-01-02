<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'description',
        'budget',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
