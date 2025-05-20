<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'country',
        'province',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];
}
