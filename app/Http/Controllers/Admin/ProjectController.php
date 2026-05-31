<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Jobs\CompressVideoJob;
use App\Models\Project;
use App\Models\ProjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ProjectController extends Controller
{
    private const WORKING_FOLDER = '99-ROBOCZE';

    public function index()
    {
        $projects = Project::with('projectType')->orderBy('created_at', 'desc')->get();

        return \Inertia\Inertia::render('Admin/Projects/Index', [
            'projects' => ProjectResource::collection($projects),
        ]);
    }

    public function create()
    {
        $types = ProjectType::query()->orderBy('name', 'asc')->get(['id', 'name', 'slug']);

        return \Inertia\Inertia::render('Admin/Projects/Create', [
            'types' => ['data' => $types],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_catalog' => 'boolean',
            'project_type_id' => 'nullable|exists:project_types,id',
            'cover_image' => 'required|image|max:20480',
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:51200',
            'video_qualities.*' => 'nullable|in:1080p,720p,480p',
        ]);

        $projectSlug = Str::slug($validated['title']);
        $clientSlug = 'portfolio';
        $workingRelative = $this->workingRelative($clientSlug, $projectSlug);
        $basePath = $this->storageAbsolute($workingRelative);

        File::ensureDirectoryExists($basePath, 0755, true);
        $this->generateUrlShortcut($basePath, $projectSlug);

        $coverFile = $request->file('cover_image');
        $coverName = 'cover-' . time() . '.' . $coverFile->getClientOriginalExtension();
        $coverFile->move($basePath, $coverName);

        $dbCoverPath = $workingRelative . '/' . $coverName;
        $thumbName = 'cover-thumb-' . time() . '.jpg';
        $dbThumbPath = $workingRelative . '/' . $thumbName;

        Image::decodePath($this->storageAbsolute($dbCoverPath))
            ->scaleDown(800)
            ->encode(new JpegEncoder(80))
            ->save($this->storageAbsolute($dbThumbPath));

        $project = Project::create([
            'title' => $validated['title'],
            'slug' => $projectSlug,
            'description' => $validated['description'] ?? null,
            'is_catalog' => $validated['is_catalog'] ?? false,
            'project_type_id' => $validated['project_type_id'] ?? null,
            'cover_image_path' => $dbCoverPath,
            'cover_thumbnail_path' => $dbThumbPath,
        ]);

        if ($request->hasFile('gallery_files')) {
            $videoQualities = $request->input('video_qualities', []);

            foreach ($request->file('gallery_files') as $index => $file) {
                $isVideo = str_starts_with($file->getMimeType(), 'video');
                $name = 'gallery-' . $index . '-' . time() . '.' . $file->getClientOriginalExtension();

                $file->move($basePath, $name);
                $dbPath = $workingRelative . '/' . $name;

                if ($isVideo) {
                    $quality = $videoQualities[$index] ?? '720p';
                    $compressedName = 'gallery-' . $index . '-' . time() . '-compressed.mp4';
                    $compressedDbPath = $workingRelative . '/' . $compressedName;

                    $media = $project->media()->create([
                        'file_path' => $dbPath,
                        'original_file_path' => $dbPath,
                        'file_type' => 'video',
                        'sort_order' => $index,
                        'processing_status' => 'processing',
                        'video_quality' => $quality,
                    ]);

                    CompressVideoJob::dispatch($media->id, $dbPath, $compressedDbPath, $quality);
                } else {
                    $project->media()->create([
                        'file_path' => $dbPath,
                        'file_type' => 'image',
                        'sort_order' => $index,
                        'processing_status' => 'ready',
                    ]);
                }
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Projekt został utworzony.');
    }

    public function edit(Project $project)
    {
        $types = ProjectType::query()->orderBy('name', 'asc')->get(['id', 'name', 'slug']);

        $project->load(['media', 'projectType']);

        return \Inertia\Inertia::render('Admin/Projects/Form', [
            'project' => new ProjectResource($project),
            'types' => ['data' => $types],
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_catalog' => 'boolean',
            'project_type_id' => 'nullable|exists:project_types,id',
            'cover_image' => 'nullable|image|max:20480',
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:51200',
            'video_qualities.*' => 'nullable|in:1080p,720p,480p',
        ]);

        $oldSlug = $project->slug;
        $newSlug = Str::slug($validated['title']);
        $clientSlug = 'portfolio';

        $oldRootRelative = $this->projectRootRelative($clientSlug, $oldSlug);
        $newRootRelative = $this->projectRootRelative($clientSlug, $newSlug);

        $oldProjectRoot = $this->storageAbsolute($oldRootRelative);
        $newProjectRoot = $this->storageAbsolute($newRootRelative);

        if ($oldSlug !== $newSlug && File::exists($oldProjectRoot)) {
            File::ensureDirectoryExists(dirname($newProjectRoot), 0755, true);
            File::move($oldProjectRoot, $newProjectRoot);

            if ($project->cover_image_path) {
                $project->cover_image_path = str_replace($oldRootRelative, $newRootRelative, $project->cover_image_path);
            }
            if ($project->cover_thumbnail_path) {
                $project->cover_thumbnail_path = str_replace($oldRootRelative, $newRootRelative, $project->cover_thumbnail_path);
            }

            foreach ($project->media as $media) {
                $payload = [
                    'file_path' => str_replace($oldRootRelative, $newRootRelative, $media->file_path),
                ];

                if ($media->original_file_path) {
                    $payload['original_file_path'] = str_replace($oldRootRelative, $newRootRelative, $media->original_file_path);
                }

                $media->update($payload);
            }
        }

        $workingRelative = $this->workingRelative($clientSlug, $newSlug);
        $basePath = $this->storageAbsolute($workingRelative);
        File::ensureDirectoryExists($basePath, 0755, true);

        if ($oldSlug !== $newSlug) {
            $this->generateUrlShortcut($basePath, $newSlug);
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $newSlug,
            'description' => $validated['description'] ?? null,
            'is_catalog' => $validated['is_catalog'] ?? false,
            'project_type_id' => $validated['project_type_id'] ?? null,
        ];

        if ($request->hasFile('cover_image')) {
            $oldCoverAbsolute = $this->resolveAbsoluteExistingPath($project->cover_image_path);
            if ($oldCoverAbsolute && File::exists($oldCoverAbsolute)) {
                File::delete($oldCoverAbsolute);
            }

            $oldThumbAbsolute = $this->resolveAbsoluteExistingPath($project->cover_thumbnail_path);
            if ($oldThumbAbsolute && File::exists($oldThumbAbsolute)) {
                File::delete($oldThumbAbsolute);
            }

            $coverFile = $request->file('cover_image');
            $coverName = 'cover-' . time() . '.' . $coverFile->getClientOriginalExtension();
            $coverFile->move($basePath, $coverName);

            $data['cover_image_path'] = $workingRelative . '/' . $coverName;

            $thumbName = 'cover-thumb-' . time() . '.jpg';
            $data['cover_thumbnail_path'] = $workingRelative . '/' . $thumbName;

            Image::decodePath($this->storageAbsolute($data['cover_image_path']))
                ->scaleDown(800)
                ->encode(new JpegEncoder(80))
                ->save($this->storageAbsolute($data['cover_thumbnail_path']));
        } elseif ($oldSlug !== $newSlug) {
            $data['cover_image_path'] = $project->cover_image_path;
            $data['cover_thumbnail_path'] = $project->cover_thumbnail_path;
        }

        $project->update($data);

        if ($request->hasFile('gallery_files')) {
            $videoQualities = $request->input('video_qualities', []);

            foreach ($request->file('gallery_files') as $index => $file) {
                $isVideo = str_starts_with($file->getMimeType(), 'video');
                $name = 'gallery-' . $index . '-' . time() . '.' . $file->getClientOriginalExtension();

                $file->move($basePath, $name);
                $dbPath = $workingRelative . '/' . $name;

                if ($isVideo) {
                    $quality = $videoQualities[$index] ?? '720p';
                    $compressedName = 'gallery-' . $index . '-' . time() . '-compressed.mp4';
                    $compressedDbPath = $workingRelative . '/' . $compressedName;

                    $media = $project->media()->create([
                        'file_path' => $dbPath,
                        'original_file_path' => $dbPath,
                        'file_type' => 'video',
                        'sort_order' => $index,
                        'processing_status' => 'processing',
                        'video_quality' => $quality,
                    ]);

                    CompressVideoJob::dispatch($media->id, $dbPath, $compressedDbPath, $quality);
                } else {
                    $project->media()->create([
                        'file_path' => $dbPath,
                        'file_type' => 'image',
                        'sort_order' => $index,
                        'processing_status' => 'ready',
                    ]);
                }
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Projekt został zaktualizowany.');
    }

    public function destroy(Project $project)
    {
        $clientSlug = 'portfolio';
        $storageProjectPath = $this->storageAbsolute($this->projectRootRelative($clientSlug, $project->slug));
        $legacyPublicPath = public_path($this->projectRootRelative($clientSlug, $project->slug));

        if (File::exists($storageProjectPath)) {
            File::deleteDirectory($storageProjectPath);
        }

        if (File::exists($legacyPublicPath)) {
            File::deleteDirectory($legacyPublicPath);
        }

        Project::query()->whereKey($project->getKey())->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Projekt został usunięty.');
    }

    public function toggleFeatured(Project $project)
    {
        $project->update([
            'is_featured' => ! $project->is_featured,
        ]);

        return back()->with('success', 'Status wyróżnienia projektu został zmieniony.');
    }

    private function generateUrlShortcut(string $absoluteFolderPath, string $slug): void
    {
        $url = route('project.show', $slug);
        $content = "[InternetShortcut]\r\nURL={$url}\r\n";

        File::put($absoluteFolderPath . '/Link-do-projektu.url', $content);
    }

    private function projectRootRelative(string $clientSlug, string $projectSlug): string
    {
        return "Projekty/{$clientSlug}/{$projectSlug}";
    }

    private function workingRelative(string $clientSlug, string $projectSlug): string
    {
        return $this->projectRootRelative($clientSlug, $projectSlug) . '/' . self::WORKING_FOLDER;
    }

    private function storageAbsolute(string $relativePath): string
    {
        return storage_path('app/public/' . ltrim($relativePath, '/'));
    }

    private function resolveAbsoluteExistingPath(?string $relativePath): ?string
    {
        if (! $relativePath) {
            return null;
        }

        $storage = $this->storageAbsolute($relativePath);
        if (File::exists($storage)) {
            return $storage;
        }

        $legacyPublic = public_path($relativePath);
        if (File::exists($legacyPublic)) {
            return $legacyPublic;
        }

        return null;
    }
}
