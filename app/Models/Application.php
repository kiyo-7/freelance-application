<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'project_id',
        'freelancer_id',
        'status',
        'proposal',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }
}
