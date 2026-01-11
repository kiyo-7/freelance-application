<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'freelancer_id',
        'description',
        'budget',
        'status',
        'client_name',      // new
        'location',         // new
        'posted_at',        // new
        'category_badge',   // new
        'proposals_count',  // new
    ];

    // Automatically cast 'posted_at' to Carbon datetime
    protected $casts = [
    'posted_at' => 'datetime',
    'budget' => 'decimal:2',
    'proposals_count' => 'integer',
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
    public function proposals()
{
    return $this->hasMany(Proposal::class);
}

public function freelancer()
{
    return $this->belongsTo(User::class, 'freelancer_id');
}

public function favoritedBy()
{
    return $this->belongsToMany(
        User::class,
        'favorite_projects',
        'project_id',
        'freelancer_id'
    );
}
   public function incrementApplicationsCount(): self
    {
        $this->increment('proposals_count');
        return $this->refresh();
    }


}
