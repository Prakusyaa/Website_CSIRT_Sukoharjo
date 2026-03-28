<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Download a secure attachment strictly enforcing Incident View policies iteratively.
     */
    public function download(Request $request, int $incidentId, int $attachmentId): StreamedResponse
    {
        $incident = Report::findOrFail($incidentId);
        $this->authorize('view', $incident);

        $attachment = Attachment::where('report_id', $incidentId)->findOrFail($attachmentId);

        if (!Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'File not found safely on local disk structure.');
        }

        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name);
    }
}
