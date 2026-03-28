<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class RoleResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'level'        => $this->level,
            'users_count'  => $this->whenCounted('users'),
            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
