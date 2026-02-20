<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'url'                => asset($this->file_path),
            'type'               => $this->type, // Uses the accessor
            'sort_order'         => $this->sort_order,
            'processing_status'  => $this->processing_status,  // 'ready' | 'processing' | 'failed'
            'video_quality'      => $this->video_quality,
        ];
    }
}
