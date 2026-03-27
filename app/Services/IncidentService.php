<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentService
{
    /**
     * Fetch a paginated array of incidents preloaded with robust operational relationships.
     */
    public function getPaginatedList(int $perPage = 15): LengthAwarePaginator
    {
        return Report::with(['category', 'severity', 'assignedUser', 'reporter'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Isolate a single fully loaded incident object cleanly.
     */
    public function getIncidentById(int $id): Report
    {
        return Report::with(['category', 'severity', 'assignedUser', 'reporter', 'creator'])
            ->findOrFail($id);
    }

    /**
     * Create a new securely mapped Incident mapped immediately to creating actors appropriately.
     */
    public function createIncident(array $data, ?int $creatorId = null): Report
    {
        $data['status'] = 'pending';
        $data['created_by'] = $creatorId;

        // Auto-assign reporter_id if the creator acts as themselves making it
        if (!isset($data['reporter_id']) && $creatorId) {
            $data['reporter_id'] = $creatorId;
        }

        $incident = Report::create($data);
        
        // TODO: Fire incident created audit event 

        return $incident;
    }

    /**
     * Mutate an existing incident enforcing pure business logic.
     */
    public function updateIncident(Report $incident, array $data): Report
    {
        $incident->update($data);

        // TODO: Map complex State transitions and record in AuditLogs

        return $incident;
    }

    /**
     * Unconditionally purge or safely permanently banish an incident object depending on scope settings if permitted.
     */
    public function deleteIncident(Report $incident): bool
    {
        // Audit log tracing here.
        return $incident->delete() ?? false;
    }
}
