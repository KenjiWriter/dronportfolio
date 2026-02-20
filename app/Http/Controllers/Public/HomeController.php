<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $projects = \App\Models\Project::with([
            'media' => function ($query) {
                $query->orderBy('sort_order');
            }
        ])->orderBy('created_at', 'desc')->get();

        return \Inertia\Inertia::render('Home', [
            'projects' => \App\Http\Resources\ProjectResource::collection($projects),
        ]);
    }
}
