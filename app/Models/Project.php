<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /**
     * @var array<int, string>
     */
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
        'form_definition_id',
        'form_responses',
    ];

    protected $casts = [
        'form_responses' => 'array',
    ];

    public function formDefinition(): BelongsTo
    {
        return $this->belongsTo(FormDefinition::class);
    }

    public function csvUploads(): HasMany
    {
        return $this->hasMany(ProjectCsvUpload::class);
    }
}
