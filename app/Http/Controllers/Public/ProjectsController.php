<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Models\ProjectType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    public function index(Request $request)
    {
        $types = ProjectType::query()->orderBy('name', 'asc')->get(['id', 'name', 'slug']);

        $projects = $this->buildQuery($request)
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $this->attachPreviewMedia($projects->getCollection());

        return \Inertia\Inertia::render('Projects', [
            'projects' => ProjectResource::collection($projects),
            'types' => ['data' => $types],
            'current_type' => $request->string('type')->toString() ?: null,
        ]);
    }

    public function apiIndex(Request $request)
    {
        $projects = $this->buildQuery($request)
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $this->attachPreviewMedia($projects->getCollection());

        return ProjectResource::collection($projects);
    }

    private function buildQuery(Request $request): Builder
    {
        $query = Project::query()->with('projectType');

        if ($request->filled('type')) {
            $typeSlug = $request->string('type')->toString();

            $query->whereHas('projectType', function (Builder $builder) use ($typeSlug) {
                $builder->where('slug', $typeSlug);
            });
        }

        return $query;
    }

    private function attachPreviewMedia(Collection $projects): void
    {
        if ($projects->isEmpty()) {
            return;
        }

        $projectIds = $projects->pluck('id')->all();

        $previewByProject = ProjectMedia::query()->where(function ($query) use ($projectIds) {
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

        $projects->each(function (Project $project) use ($previewByProject) {
            $project->setRelation('media', $previewByProject->get($project->id, collect()));
        });
    }
}
