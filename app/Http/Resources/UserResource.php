<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'username'   => $this->username,
            'email'      => $this->email,
            'is_active'  => (bool) $this->is_active,
            'role'       => $this->whenLoaded('role', fn () => [
                'id'    => $this->role->id,
                'name'  => $this->role->name,
                'level' => $this->role->level,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
