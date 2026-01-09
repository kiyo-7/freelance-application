<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'freelancer_id',
        'bid_amount',
        'delivery_time',
        'cover_letter',
        'status',
        'is_shortlisted',
        'submitted_at',
    ];

    protected $casts = [
        'bid_amount'    => 'decimal:2',
        'delivery_time' => 'integer',
        'is_shortlisted'=> 'boolean',
        'submitted_at'  => 'datetime',
    ];

    /* =====================
     |  Relationships
     ===================== */

    // Proposal → Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Proposal → Freelancer (User)
    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    /* =====================
     |  Query Scopes
     ===================== */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /* =====================
     |  Helpers
     ===================== */

    public function accept()
    {
        $this->update(['status' => 'accepted']);

        // Optional: auto-update project status
        if ($this->project) {
            $this->project->update(['status' => 'in_progress']);
        }
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
    }
}
