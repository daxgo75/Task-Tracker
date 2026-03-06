<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'project_id'  => $this->project_id,
            'title'       => $this->title,
            'description' => $this->description,
            'due_date'    => $this->due_date?->toDateString(),
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'created_by'  => new UserResource($this->whenLoaded('creator')),
            'project'     => new ProjectBriefResource($this->whenLoaded('project')),
            'is_deleted'  => $this->isDeleted(),
            'deleted_at'  => $this->deleted_at?->toDateTimeString(),
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
