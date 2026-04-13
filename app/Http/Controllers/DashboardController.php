<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Total incidents across the system
        $totalIncidents = Report::count();

        // Incidents actively being triaged or worked on
        $openIncidents = Report::whereIn('status', ['pending', 'validated', 'in_progress'])->count();

        // Incidents completed
        $resolvedIncidents = Report::where('status', 'resolved')->count();

        // Critical incidents where severity mapping >= 60
        $criticalIncidents = Report::whereHas('severity', function ($query) {
            $query->where('level', '>=', 60);
        })->count();

        // Incidents created in the last 7 days
        $recentIncidents = Report::where('created_at', '>=', now()->subDays(7))->count();

        // Granular aggregation of incident quantity grouped by Category (no PII fetched)
        $incidentsByCategory = Report::selectRaw('categories.name as category, count(reports.id) as total')
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_incidents' => $totalIncidents,
                'open_incidents' => $openIncidents,
                'resolved_incidents' => $resolvedIncidents,
                'critical_incidents' => $criticalIncidents,
                'incidents_last_7_days' => $recentIncidents,
                'incidents_by_category' => $incidentsByCategory,
            ],
        ]);
    }
}
