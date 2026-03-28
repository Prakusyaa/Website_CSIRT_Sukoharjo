<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AttachmentResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'report_id'   => $this->report_id,
            'file_name'   => $this->file_name,
            'file_type'   => $this->file_type,
            'file_size'   => $this->file_size,
            // Never expose the raw storage path — provide a signed download URL only
            'download_url' => route('incidents.attachments.download', [
                'incident'   => $this->report_id,
                'attachment' => $this->id,
            ]),
            'uploaded_by' => $this->whenLoaded('creator', fn () => [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'created_at'  => $this->created_at?->toISOString(),
        ];
    }
}
