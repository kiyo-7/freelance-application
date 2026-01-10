<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /* =====================
       Relationships
    ====================== */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function favoritedBy()
{
    return $this->belongsToMany(
        User::class,
        'favorite_services',
        'service_id',
        'user_id'
    );
}

}

 ?>