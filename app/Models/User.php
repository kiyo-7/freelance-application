<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // -------------------------
    // Mass assignable
    // -------------------------
    protected $fillable = [
        'email',
        'password',
        'role',
        'is_verified',
    ];

    // -------------------------
    // Hidden fields
    // -------------------------
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // -------------------------
    // Casts
    // -------------------------
    protected $casts = [
        'is_verified' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    // -------------------------
    // Role-based profiles
    // -------------------------
    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function freelancerProfile()
    {
        return $this->hasOne(FreelancerProfile::class);
    }

    // -------------------------
    // Other relationships
    // -------------------------
    public function projects()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'freelancer_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function paymentsSent()
    {
        return $this->hasMany(Payment::class, 'payer_id');
    }

    public function paymentsReceived()
    {
        return $this->hasMany(Payment::class, 'payee_id');
    }
}
