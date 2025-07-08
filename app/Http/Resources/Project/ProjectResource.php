<?php

namespace App\Http\Resources\Project;

use App\Http\Resources\Attachment\AttachmentResource;
use App\Http\Resources\Comment\CommentResource;
use App\Http\Resources\Task\TaskResource;
use App\Http\Resources\Team\TeamResource;
use App\Http\Resources\User\UserResource;
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
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'team' => new TeamResource($this->whenLoaded('team')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'completed_tasks_count' => $this->completed_tasks_count,
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
