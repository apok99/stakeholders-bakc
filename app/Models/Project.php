<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'project_context',
        'promoting_company',
        'brief_project_description',
        'location',
        'current_phase',
        'main_objective',
        'perceived_sensitive_issues',
        'known_initial_actors',
        'next_milestones',
        'reference_links',
    ];
}
