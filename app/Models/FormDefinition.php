<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormDefinition extends Model
{
    protected $fillable = ['name', 'fields'];

    protected $casts = [
        'fields' => 'array',
    ];
}
