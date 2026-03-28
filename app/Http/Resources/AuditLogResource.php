<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AuditLogResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'action'     => $this->action,
            'table_name' => $this->table_name,
            'record_id'  => $this->record_id,
            'changes'    => $this->changes, // Already cast to array by the model
            'actor'      => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
