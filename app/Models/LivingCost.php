<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivingCost extends Model
{
    protected $fillable = [
        'city_id',
        'housing',
        'food',
        'transportation',
        'utilities',
        'healthcare',
        'entertainment',
        'other',
        'total_estimation',
        'user_id'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
