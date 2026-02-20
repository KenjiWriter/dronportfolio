<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMedia extends Model
{
    protected $fillable = [
        'project_id',
        'file_path',
        'file_type',
        'sort_order',
        'processing_status',
        'video_quality',
        'original_file_path',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getTypeAttribute()
    {
        $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? 'image' : 'video';
    }
}
