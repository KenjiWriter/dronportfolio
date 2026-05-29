<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Load only projects – no media eager-loaded here.
        // We then attach at most 2 ready preview items per project in a single query,
        // instead of pulling every media row and discarding them in PHP.
        $projects = \App\Models\Project::orderBy('created_at', 'desc')->get();

        if ($projects->isNotEmpty()) {
            $projectIds = $projects->pluck('id')->all();

            $previewByProject = \App\Models\ProjectMedia::whereIn('project_id', $projectIds)
                ->where('processing_status', 'ready')
                ->orderBy('project_id')
                ->orderBy('sort_order')
                ->select(['id', 'project_id', 'file_path', 'file_type', 'sort_order', 'processing_status'])
                ->get()
                ->groupBy('project_id')
                ->map(fn ($group) => $group->take(2)->values());

            $projects->each(function ($project) use ($previewByProject) {
                $project->setRelation('media', $previewByProject->get($project->id, collect()));
            });
        }

        return \Inertia\Inertia::render('Home', [
            'projects' => \App\Http\Resources\ProjectResource::collection($projects),
        ]);
    }
}
