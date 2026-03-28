<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * Display a paginated listing of system-wide Audit Logs strictly for Administrative oversight.
     */
    public function index(Request $request): Response
    {
        // Enforce strict authorization block natively via UserPolicy / Gate mappings. 
        // We defer to the global Gate::before rule intercepting isAdmin() or explicitly fail.
        if (! $request->user()->isAdmin()) {
            abort(403, 'These records contain highly sensitive historical data restricted to System Administrators.');
        }

        $logs = AuditLog::with('user:id,name,email,role_id')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
        ]);
    }
}
