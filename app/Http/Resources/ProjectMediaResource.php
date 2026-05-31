<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProjectMediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $url = Storage::disk('public')->exists($this->file_path)
            ? asset('storage/' . ltrim($this->file_path, '/'))
            : asset($this->file_path);

        return [
            'id'                 => $this->id,
            'url'                => $url,
            'type'               => $this->type, // Uses the accessor
            'sort_order'         => $this->sort_order,
            'processing_status'  => $this->processing_status,  // 'ready' | 'processing' | 'failed'
            'video_quality'      => $this->video_quality,
        ];
    }
}
