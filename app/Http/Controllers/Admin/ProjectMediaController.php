<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectMedia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProjectMediaController extends Controller
{
    public function destroy(ProjectMedia $projectMedia)
    {
        if (Storage::disk('public')->exists($projectMedia->file_path)) {
            Storage::disk('public')->delete($projectMedia->file_path);
        } else {
            $legacyPath = public_path($projectMedia->file_path);
            if (File::exists($legacyPath)) {
                File::delete($legacyPath);
            }
        }

        // Delete DB record
        ProjectMedia::query()->whereKey($projectMedia->getKey())->delete();

        return back()->with('success', 'Plik został usunięty.');
    }
}
