<?php

namespace App\Http\Resources\Attachment;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
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
            'file_name' => $this->file_name,
            'file_size_kb' => round($this->file_size / 1024, 2),
            'mime_type' => $this->mime_type,
            'uploaded_by' => new UserResource($this->whenLoaded('uploader')),
        ];

    }
}
