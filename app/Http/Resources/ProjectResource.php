<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_catalog' => $this->is_catalog,
            'cover_image_url' => \Illuminate\Support\Facades\Storage::url($this->cover_image_path),
            'media' => ProjectMediaResource::collection($this->whenLoaded('media')),
            'created_at' => $this->created_at,
        ];
    }
}
