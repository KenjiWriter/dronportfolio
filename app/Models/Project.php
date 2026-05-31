<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_catalog',
        'is_featured',
        'project_type_id',
        'cover_image_path',
        'cover_thumbnail_path',
    ];

    protected $casts = [
        'is_catalog' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function media(): HasMany
    {
        return $this->hasMany(ProjectMedia::class);
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }
}
