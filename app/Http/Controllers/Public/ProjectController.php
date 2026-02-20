<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function show($slug)
    {
        $project = \App\Models\Project::where('slug', $slug)
            ->with([
                'media' => function ($query) {
                    // Only serve fully-compressed/ready media to the public
                    $query->where('processing_status', 'ready')
                          ->orderBy('sort_order');
                }
            ])
            ->firstOrFail();

        return \Inertia\Inertia::render('Project/Show', [
            'project' => new \App\Http\Resources\ProjectResource($project),
        ]);
    }
}
