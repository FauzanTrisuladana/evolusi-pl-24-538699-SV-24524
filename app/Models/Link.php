<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $primaryKey = 'id_link';

    protected $fillable = [
        'name',
        'true_link',
        'new_link',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
