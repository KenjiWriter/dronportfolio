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
                    $query->orderBy('sort_order');
                }
            ])
            ->firstOrFail();

        return \Inertia\Inertia::render('Project/Show', [
            'project' => new \App\Http\Resources\ProjectResource($project),
        ]);
    }
}
