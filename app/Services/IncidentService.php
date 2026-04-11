<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Report;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentService
{
    /**
     * Fetch a paginated array of incidents preloaded with robust operational relationships.
     * Supports dynamic filtering (search, status) and strict column sorting protocols.
     */
    public function getPaginatedList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Report::with(['category', 'severity', 'assignedUser', 'reporter']);

        // Handle text-based search (Subject or Email)
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('subject', 'like', '%'.$filters['search'].'%')
                    ->orWhere('reporter_email', 'like', '%'.$filters['search'].'%');
            });
        }

        // Handle specific status filter exactly via enum values
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Handle date range filtering on created_at (date-only, ignores time component)
        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Safe Sort Processing (whitelist sorting columns dynamically preventing injection!)
        $sortColumn = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        $allowedSorts = ['id', 'subject', 'status', 'created_at', 'severity_id'];
        $safeSortColumn = in_array($sortColumn, $allowedSorts) ? $sortColumn : 'created_at';
        $safeSortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'desc';

        return $query->orderBy($safeSortColumn, $safeSortDirection)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Isolate a single fully loaded incident object cleanly.
     */
    public function getIncidentById(int $id): Report
    {
        return Report::with(['category', 'severity', 'assignedUser', 'reporter', 'creator', 'attachments'])
            ->findOrFail($id);
    }

    /**
     * Create a new securely mapped Incident mapped immediately to creating actors appropriately.
     */
    public function createIncident(array $data, ?int $creatorId = null): Report
    {
        $data['status'] = 'pending';
        $data['created_by'] = $creatorId;

        // Enforce mutual-exclusivity: null out the field that does not apply
        // to the chosen reporter_type so the DB CHECK constraint is satisfied.
        if (($data['reporter_type'] ?? null) === 'internal') {
            $data['reporter_email'] = null;
        } elseif (($data['reporter_type'] ?? null) === 'external') {
            $data['reporter_id'] = null;
        }

        // Extract binary arrays effectively saving to disk later
        $attachments = [];
        if (isset($data['attachments'])) {
            $attachments = $data['attachments'];
            unset($data['attachments']);
        }

        $incident = Report::create($data);

        // Ensure robustly persisted disk binaries map seamlessly with Attachment relationships preventing public-facing URL exposures natively
        if (! empty($attachments)) {
            foreach ($attachments as $file) {
                if ($file->isValid()) {
                    $path = $file->store('incidents/'.$incident->id, 'local');

                    Attachment::create([
                        'report_id' => $incident->id,
                        'file_name' => collect(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->take(255)->implode('').'.'.$file->getClientOriginalExtension(),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'createdby' => $creatorId,
                    ]);
                }
            }
        }

        return $incident;
    }

    /**
     * Mutate an existing incident enforcing pure business logic.
     */
    public function updateIncident(Report $incident, array $data): Report
    {
        // If reporter_type is being changed, null out the unused field
        if (isset($data['reporter_type'])) {
            if ($data['reporter_type'] === 'internal') {
                $data['reporter_email'] = null;
            } elseif ($data['reporter_type'] === 'external') {
                $data['reporter_id'] = null;
            }
        }

        $incident->update($data);

        return $incident;
    }

    /**
     * Unconditionally purge or safely permanently banish an incident object depending on scope settings if permitted.
     */
    public function deleteIncident(Report $incident): bool
    {
        return $incident->delete() ?? false;
    }
}
