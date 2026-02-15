<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectMedia;
use Illuminate\Support\Facades\File;

class ProjectMediaController extends Controller
{
    public function destroy(ProjectMedia $projectMedia)
    {
        // Delete physical file
        if (File::exists(public_path($projectMedia->file_path))) {
            File::delete(public_path($projectMedia->file_path));
        }

        // Delete DB record
        $projectMedia->delete();

        return back()->with('success', 'Plik został usunięty.');
    }
}
