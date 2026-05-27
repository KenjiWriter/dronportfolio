<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Load only the first 2 ready media items per project for the stack-preview.
        // Full media is fetched on-demand via /api/projects/{slug}/media.
        $projects = \App\Models\Project::with([
            'media' => function ($query) {
                $query->where('processing_status', 'ready')
                      ->orderBy('sort_order');
            }
        ])->orderBy('created_at', 'desc')->get();

        return \Inertia\Inertia::render('Home', [
            'projects' => \App\Http\Resources\ProjectResource::collection($projects),
        ]);
    }
}
