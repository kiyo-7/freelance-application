<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'freelancer_id',
        'project_id',
    ];

    /* ================= Relationships ================= */

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
