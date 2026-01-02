<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'project_id',
        'payer_id',
        'payee_id',
        'amount',
        'status',
        'transaction_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
}
