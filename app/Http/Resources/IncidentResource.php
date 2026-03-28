<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class IncidentResource extends BaseResource
{
    /**
     * Transform the resource into an array standardized for frontend and API consumption.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'reporter_email' => $this->reporter_email,
            
            // Loaded Relationships
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'severity' => $this->whenLoaded('severity', fn () => [
                'id' => $this->severity->id,
                'name' => $this->severity->name,
                'level' => $this->severity->level,
            ]),
            'reporter' => $this->whenLoaded('reporter', fn () => [
                'id' => $this->reporter->id,
                'name' => $this->reporter->name,
                'username' => $this->reporter->username,
            ]),
            'assignee' => $this->whenLoaded('assignedUser', fn () => [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
            ]),
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),

            // ISO UTC Timestamps mapped functionally
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
