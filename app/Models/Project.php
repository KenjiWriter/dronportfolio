<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_catalog',
        'cover_image_path',
        'cover_thumbnail_path',
    ];

    public function media()
    {
        return $this->hasMany(ProjectMedia::class);
    }
}
