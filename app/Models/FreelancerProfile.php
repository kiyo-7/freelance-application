<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreelancerProfile extends Model
{

    protected $fillable = [
        'user_id',
        'full_name',
        'professional_title',
        'location',
        'avatar_url',
        'is_online',
        'is_verified',
        'rating',
        'total_reviews',
        'completed_jobs',
        'response_time',
        'bio',
        'years_of_experience',
        'languages',
        'skills',
        'services',
        'portfolio',
        'reviews',
        'rating_distribution',
        'hourlyRate'
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'is_verified' => 'boolean',
        'rating' => 'float',
        'languages' => 'array',
        'skills' => 'array',
        'services' => 'array',
        'portfolio' => 'array',
        'reviews' => 'array',
        'rating_distribution' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
