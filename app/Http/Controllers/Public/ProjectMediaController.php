<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectMediaResource;
use App\Models\Project;

class ProjectMediaController extends Controller
{
    public function index(string $slug)
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        $media = $project->media()
            ->where('processing_status', 'ready')
            ->orderBy('sort_order')
            ->get();

        return ProjectMediaResource::collection($media);
    }
}
