<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectTypeController extends Controller
{
    public function index()
    {
        $types = ProjectType::query()
            ->withCount('projects')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'slug']);

        return \Inertia\Inertia::render('Admin/ProjectTypes/Index', [
            'types' => ['data' => $types],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:project_types,name',
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 2;

        while (ProjectType::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        ProjectType::create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return back()->with('success', 'Typ realizacji został dodany.');
    }

    public function update(Request $request, ProjectType $projectType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:project_types,name,' . $projectType->id,
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 2;

        while (ProjectType::query()
            ->where('slug', $slug)
            ->whereKeyNot($projectType->id)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        ProjectType::query()->whereKey($projectType->id)->update([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return back()->with('success', 'Typ realizacji został zaktualizowany.');
    }

    public function destroy(ProjectType $projectType)
    {
        if ($projectType->projects()->exists()) {
            return back()->withErrors([
                'type' => 'Nie mozna usunac typu, ktory jest przypisany do realizacji.',
            ]);
        }

        ProjectType::query()->whereKey($projectType->getKey())->delete();

        return back()->with('success', 'Typ realizacji został usunięty.');
    }
}
