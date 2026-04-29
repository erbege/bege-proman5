<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeeklyReportResource extends JsonResource
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
            'week_number' => $this->week_number,
            'period_start' => $this->period_start?->format('Y-m-d'),
            'period_end' => $this->period_end?->format('Y-m-d'),
            'cover_title' => $this->cover_title,
            'status' => $this->status,
            'activities' => $this->activities,
            'problems' => $this->problems,
            'cumulative_data' => $this->cumulative_data,
            'detail_data' => $this->detail_data,
            'documentation_uploads' => $this->documentation_uploads,
            'creator' => [
                'id' => $this->created_by,
                'name' => $this->creator?->name,
            ],
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
