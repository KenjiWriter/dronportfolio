<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = \App\Models\Project::orderBy('created_at', 'desc')->get();
        return \Inertia\Inertia::render('Admin/Projects/Index', [
            'projects' => \App\Http\Resources\ProjectResource::collection($projects),
        ]);
    }

    public function create()
    {
        return \Inertia\Inertia::render('Admin/Projects/Create');
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_catalog' => 'boolean',
            'cover_image' => 'required|image|max:20480', // 20MB
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:51200', // 50MB
        ]);

        $coverPath = $request->file('cover_image')->store('projects/covers', 'public');

        $project = \App\Models\Project::create([
            'title' => $validated['title'],
            'slug' => \Illuminate\Support\Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'is_catalog' => $validated['is_catalog'] ?? false,
            'cover_image_path' => $coverPath,
        ]);

        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $file) {
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
                $path = $file->store('projects/gallery', 'public');

                $project->media()->create([
                    'file_path' => $path,
                    'file_type' => $type,
                    'sort_order' => 0,
                ]);
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Projekt został utworzony.');
    }

    public function edit(\App\Models\Project $project)
    {
        $project->load('media');
        return \Inertia\Inertia::render('Admin/Projects/Edit', [
            'project' => new \App\Http\Resources\ProjectResource($project),
        ]);
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_catalog' => 'boolean',
            'cover_image' => 'nullable|image|max:20480',
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:51200',
        ]);

        $data = [
            'title' => $validated['title'],
            'slug' => \Illuminate\Support\Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'is_catalog' => $validated['is_catalog'] ?? false,
        ];

        if ($request->hasFile('cover_image')) {
            // Optionally delete old cover
            // \Illuminate\Support\Facades\Storage::disk('public')->delete($project->cover_image_path);
            $data['cover_image_path'] = $request->file('cover_image')->store('projects/covers', 'public');
        }

        $project->update($data);

        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $file) {
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
                $path = $file->store('projects/gallery', 'public');

                $project->media()->create([
                    'file_path' => $path,
                    'file_type' => $type,
                    'sort_order' => 0,
                ]);
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Projekt został zaktualizowany.');
    }

    public function destroy(\App\Models\Project $project)
    {
        // cleanup files could be added here
        $project->delete();
        return redirect()->route('admin.projects.index')->with('success', 'Projekt został usunięty.');
    }
}
