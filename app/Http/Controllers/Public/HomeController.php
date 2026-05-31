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
        $projects = \App\Models\Project::with('projectType')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($projects->isNotEmpty()) {
            $projectIds = $projects->pluck('id')->all();

            $previewByProject = \App\Models\ProjectMedia::query()->where(function ($query) use ($projectIds) {
                    foreach ($projectIds as $index => $projectId) {
                        if ($index === 0) {
                            $query->where('project_id', $projectId);
                            continue;
                        }

                        $query->orWhere('project_id', $projectId);
                    }
                })
                ->where('processing_status', 'ready')
                ->orderBy('project_id', 'asc')
                ->orderBy('sort_order', 'asc')
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
