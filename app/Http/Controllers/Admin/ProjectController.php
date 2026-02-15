<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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
            'client_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_catalog' => 'boolean',
            'cover_image' => 'required|image|max:20480', // 20MB
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:51200', // 50MB
        ]);

        $projectTitle = $validated['title'];
        $projectSlug = Str::slug($projectTitle);
        $clientSlug = 'portfolio'; // Default or derived from client_name if added later

        // Define Base Path
        $basePath = public_path("Projekty/{$clientSlug}/{$projectSlug}/99-ROBOCZE");
        File::ensureDirectoryExists($basePath, 0755, true);

        // Generate .url Shortcut
        $this->generateUrlShortcut($basePath, $projectSlug);

        // Handle Cover Image
        $coverFile = $request->file('cover_image');
        $coverName = 'cover-' . time() . '.' . $coverFile->getClientOriginalExtension();
        $coverFile->move($basePath, $coverName);

        // Save Relative Path to DB
        $dbCoverPath = "Projekty/{$clientSlug}/{$projectSlug}/99-ROBOCZE/{$coverName}";

        $project = \App\Models\Project::create([
            'title' => $validated['title'],
            'slug' => $projectSlug,
            'description' => $validated['description'] ?? null,
            'is_catalog' => $validated['is_catalog'] ?? false,
            'cover_image_path' => $dbCoverPath,
        ]);

        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $index => $file) {
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
                $name = 'gallery-' . $index . '-' . time() . '.' . $file->getClientOriginalExtension();

                $file->move($basePath, $name);
                $dbPath = "Projekty/{$clientSlug}/{$projectSlug}/99-ROBOCZE/{$name}";

                $project->media()->create([
                    'file_path' => $dbPath,
                    'file_type' => $type,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Projekt został utworzony.');
    }

    public function edit(\App\Models\Project $project)
    {
        $project->load('media');
        return \Inertia\Inertia::render('Admin/Projects/Form', [
            'project' => new \App\Http\Resources\ProjectResource($project),
        ]);
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\Project $project)
    {
        \Illuminate\Support\Facades\Log::info('Project Update Request:', $request->all());
        \Illuminate\Support\Facades\Log::info('Project Update Files:', $request->allFiles());
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_catalog' => 'boolean',
            'cover_image' => 'nullable|image|max:20480',
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:51200',
        ]);

        $oldSlug = $project->slug;
        $newSlug = Str::slug($validated['title']);
        $clientSlug = 'portfolio';

        // Paths
        $oldPath = public_path("Projekty/{$clientSlug}/{$oldSlug}/99-ROBOCZE");
        $newPath = public_path("Projekty/{$clientSlug}/{$newSlug}/99-ROBOCZE");

        // Handle Slug Change -> Rename Directory
        if ($oldSlug !== $newSlug && File::exists($oldPath)) {
            // Create parent dir if missing
            File::ensureDirectoryExists(dirname($newPath), 0755, true);

            // Move the entire project folder (Projekty/portfolio/{slug})
            $oldProjectRoot = public_path("Projekty/{$clientSlug}/{$oldSlug}");
            $newProjectRoot = public_path("Projekty/{$clientSlug}/{$newSlug}");

            if (File::exists($oldProjectRoot)) {
                File::move($oldProjectRoot, $newProjectRoot);
            }

            // Update DB Paths
            $project->cover_image_path = str_replace($oldSlug, $newSlug, $project->cover_image_path);
            foreach ($project->media as $media) {
                $media->update([
                    'file_path' => str_replace($oldSlug, $newSlug, $media->file_path)
                ]);
            }
        }

        // Ensure new path exists (in case it's new or just moved)
        $basePath = $newPath;
        File::ensureDirectoryExists($basePath, 0755, true);

        // Regenerate .url shortcut if slug changed
        if ($oldSlug !== $newSlug) {
            $this->generateUrlShortcut($basePath, $newSlug);
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $newSlug,
            'description' => $validated['description'] ?? null,
            'is_catalog' => $validated['is_catalog'] ?? false,
        ];

        // Handle New Cover Image
        if ($request->hasFile('cover_image')) {
            // Delete old cover
            if ($project->cover_image_path && File::exists(public_path($project->cover_image_path))) {
                File::delete(public_path($project->cover_image_path));
            }

            $coverFile = $request->file('cover_image');
            $coverName = 'cover-' . time() . '.' . $coverFile->getClientOriginalExtension();
            $coverFile->move($basePath, $coverName);
            $data['cover_image_path'] = "Projekty/{$clientSlug}/{$newSlug}/99-ROBOCZE/{$coverName}";
        }
        else {
            // Keep distinct logic: if we renamed, we already updated cover_image_path in memory/DB, but $data will overwrite.
            // Ensure we safeguard the potentially updated path.
            if ($oldSlug !== $newSlug) {
                $data['cover_image_path'] = $project->cover_image_path;
            }
        }

        $project->update($data);

        // Handle New Gallery Files
        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $index => $file) {
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
                $name = 'gallery-' . $index . '-' . time() . '.' . $file->getClientOriginalExtension();

                $file->move($basePath, $name);
                $dbPath = "Projekty/{$clientSlug}/{$newSlug}/99-ROBOCZE/{$name}";

                $project->media()->create([
                    'file_path' => $dbPath,
                    'file_type' => $type,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Projekt został zaktualizowany.');
    }

    public function destroy(\App\Models\Project $project)
    {
        // Optional: delete files on destroy
        // File::deleteDirectory(public_path("Projekty/portfolio/{$project->slug}"));
        $project->delete();
        return redirect()->route('admin.projects.index')->with('success', 'Projekt został usunięty.');
    }

    /**
     * Generate a .url shortcut file for Windows.
     */
    private function generateUrlShortcut($path, $slug)
    {
        $url = route('project.show', $slug); // Make sure this route exists or use url("/project/{$slug}")
        $content = "[InternetShortcut]\r\nURL={$url}\r\n";
        File::put("{$path}/Link-do-projektu.url", $content);
    }
}
