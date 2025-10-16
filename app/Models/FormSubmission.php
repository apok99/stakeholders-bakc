<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_definition_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
