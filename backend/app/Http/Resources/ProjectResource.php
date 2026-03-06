<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'owner'       => new UserResource($this->whenLoaded('creator')),
            'task_counts' => $this->when(
                $this->relationLoaded('activeTasks'),
                fn () => [
                    'total' => $this->activeTasks->count(),
                ]
            ),
            'tasks'       => TaskResource::collection($this->whenLoaded('activeTasks')),
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
