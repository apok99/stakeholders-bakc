<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCsvUpload extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'form_definition_id',
        'storage_disk',
        'file_path',
        'original_name',
        'status',
        'processing_payload',
        'processed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'processing_payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function formDefinition(): BelongsTo
    {
        return $this->belongsTo(FormDefinition::class);
    }
}
