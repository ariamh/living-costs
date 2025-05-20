<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'original_name', 'stored_name', 'mime_type', 'size'
    ];
}
