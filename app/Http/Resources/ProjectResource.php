<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'title'                => $this->title,
            'slug'                 => $this->slug,
            'description'          => $this->description,
            'is_catalog'           => $this->is_catalog,
            'is_featured'          => $this->is_featured,
            'project_type_id'      => $this->project_type_id,
            'cover_image_path'     => $this->cover_image_path,
            'cover_thumbnail_path' => $this->cover_thumbnail_path,
            'cover_image_url'      => $this->resolvePublicUrl($this->cover_image_path),
            'cover_thumbnail_url'  => $this->resolvePublicUrl($this->cover_thumbnail_path),
            'type'                 => $this->whenLoaded('projectType', function () {
                if (! $this->projectType) {
                    return null;
                }

                return [
                    'id' => $this->projectType->id,
                    'name' => $this->projectType->name,
                    'slug' => $this->projectType->slug,
                ];
            }),
            // Only the first 2 items – used for the stack-preview in the grid.
            // Full media is fetched on-demand when a project card is opened.
            'preview_media'        => ProjectMediaResource::collection(
                $this->whenLoaded('media', fn () => $this->media->take(2))
            ),
            'created_at'           => $this->created_at,
        ];
    }

    private function resolvePublicUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . ltrim($path, '/'));
        }

        return asset($path);
    }
}
