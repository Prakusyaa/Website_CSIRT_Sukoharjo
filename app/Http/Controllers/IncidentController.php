<?php

namespace App\Http\Controllers;

use App\Http\Requests\Incident\StoreIncidentRequest;
use App\Http\Requests\Incident\UpdateIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Category;
use App\Models\Report;
use App\Models\Severity;
use App\Models\User;
use App\Services\IncidentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncidentController extends Controller
{
    public function __construct(
        private readonly IncidentService $incidentService
    ) {}

    /**
     * Display a listing of incidents.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Report::class);

        $filters = $request->only(['search', 'status', 'sort', 'direction', 'start_date', 'end_date']);
        $incidents = $this->incidentService->getPaginatedList($filters);

        return Inertia::render('Incidents/Index', [
            'incidents' => IncidentResource::collection($incidents),
            'filters'   => $filters,
        ]);
    }

    /**
     * Show the form for creating a new incident.
     */
    public function create(): Response
    {
        $this->authorize('create', Report::class);

        return Inertia::render('Incidents/Create', [
            ...$this->incidentFormOptions(),
        ]);
    }

    /**
     * Store a newly created incident in storage.
     */
    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $incident = $this->incidentService->createIncident(
            $request->validated(),
            $request->user()->id
        );

        return redirect()->route('incidents.show', $incident->id)
            ->with('success', 'Incident securely logged.');
    }

    /**
     * Display the specified incident.
     */
    public function show(int $id): Response
    {
        $incident = $this->incidentService->getIncidentById($id);

        $this->authorize('view', $incident);

        return Inertia::render('Incidents/Show', [
            // Resolve to a plain array — passing JsonResource to Inertia wraps payload in { data, success }.
            'incident' => (new IncidentResource($incident))->resolve(request()),
        ]);
    }

    /**
     * Show the form for editing the specified incident.
     */
    public function edit(int $id): Response
    {
        $incident = $this->incidentService->getIncidentById($id);

        $this->authorize('update', $incident);

        return Inertia::render('Incidents/Edit', [
            'incident' => (new IncidentResource($incident))->resolve(request()),
            ...$this->incidentFormOptions(includeUsers: false),
        ]);
    }

    /**
     * Update the specified incident in storage.
     */
    public function update(UpdateIncidentRequest $request, int $id): RedirectResponse
    {
        $incident = $this->incidentService->getIncidentById($id);

        // Authorization is already handled beautifully natively via UpdateIncidentRequest!
        $this->incidentService->updateIncident($incident, $request->validated());

        return redirect()->route('incidents.show', $incident->id)
            ->with('success', 'Incident updated successfully.');
    }

    /**
     * Remove the specified incident from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $incident = $this->incidentService->getIncidentById($id);

        $this->authorize('delete', $incident);

        $this->incidentService->deleteIncident($incident);

        return redirect()->route('incidents.index')
            ->with('success', 'Incident deleted/archived successfully.');
    }

    /**
     * Shared option payload for incident create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function incidentFormOptions(bool $includeUsers = true): array
    {
        $options = [
            'categories' => Category::query()
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'severities' => Severity::query()
                ->select('id', 'name', 'level')
                ->orderBy('level')
                ->get(),
            'csirtUsers' => User::active()->csirt()->select('id', 'name')->get(),
        ];

        if ($includeUsers) {
            $options['users'] = User::active()->select('id', 'name', 'email')->get();
        }

        return $options;
    }
}
