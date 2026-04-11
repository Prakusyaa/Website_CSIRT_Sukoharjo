<?php

use App\Http\Controllers\Admin\ReferenceTaxonomyController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\IncidentController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Root: redirect authenticated users based on role, guests to login
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    /** @var User $user */
    $user = Auth::user();
    $level = $user->role?->level ?? 0;

    return $level >= 100
        ? redirect('/dashboard')
        : redirect('/incidents');
});

// Guest-only routes
Route::middleware('guest')->group(function () {
    Route::get('login', [SessionController::class, 'create'])->name('login');
    Route::post('login', [SessionController::class, 'store']);
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::post('logout', [SessionController::class, 'destroy'])->name('logout');

    // Dashboard — accessible by all authenticated users
    Route::get('/dashboard', function () {
        $totalIncidents = \App\Models\Report::count();
        $openIncidents = \App\Models\Report::whereIn('status', ['pending', 'validated', 'in_progress'])->count();
        $resolvedIncidents = \App\Models\Report::where('status', 'resolved')->count();
        $criticalIncidents = \App\Models\Report::whereHas('severity', function ($query) {
            $query->where('level', '>=', 60);
        })->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_incidents' => $totalIncidents,
                'open_incidents' => $openIncidents,
                'resolved_incidents' => $resolvedIncidents,
                'critical_incidents' => $criticalIncidents,
            ]
        ]);
    })->name('dashboard');

    Route::get('/inbox', function () {
        return Inertia::render('Inbox');
    })->name('inbox');

    // Incidents — Staff can view only; CSIRT+ can create/edit/delete
    // Incidents — Accessible by Staff; creation by CSIRT+
    Route::middleware('role:staff')->group(function () {
        Route::get('incidents', [IncidentController::class, 'index'])->name('incidents.index');
    });

    Route::middleware('role:csirt')->group(function () {
        Route::get('incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
        Route::post('incidents', [IncidentController::class, 'store'])->name('incidents.store');
        Route::get('incidents/{incident}/edit', [IncidentController::class, 'edit'])->name('incidents.edit');
        Route::put('incidents/{incident}', [IncidentController::class, 'update'])->name('incidents.update');
        Route::patch('incidents/{incident}', [IncidentController::class, 'update']);
        Route::delete('incidents/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');
    });

    // Moved parameter route to the end to prevent greedy matching conflicts (e.g. incidents/create)
    Route::middleware('role:staff')->group(function () {
        Route::get('incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
    });

    // Categories & severities CRUD — role level strictly above CSIRT (> 50), e.g. administrators
    Route::middleware('role:above_csirt')->group(function () {
        Route::get('admin/reference-data', [ReferenceTaxonomyController::class, 'index'])->name('admin.reference-data.index');
        Route::post('admin/categories', [ReferenceTaxonomyController::class, 'storeCategory'])->name('admin.categories.store');
        Route::put('admin/categories/{category}', [ReferenceTaxonomyController::class, 'updateCategory'])->name('admin.categories.update');
        Route::delete('admin/categories/{category}', [ReferenceTaxonomyController::class, 'destroyCategory'])->name('admin.categories.destroy');
        Route::post('admin/severities', [ReferenceTaxonomyController::class, 'storeSeverity'])->name('admin.severities.store');
        Route::put('admin/severities/{severity}', [ReferenceTaxonomyController::class, 'updateSeverity'])->name('admin.severities.update');
        Route::delete('admin/severities/{severity}', [ReferenceTaxonomyController::class, 'destroySeverity'])->name('admin.severities.destroy');
    });

    // Administrative routes — requires Admin role (level 100)
    Route::middleware('role:admin')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::resource('admin/users', UserController::class)->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);
        Route::resource('admin/roles', RoleController::class)->except(['show'])->names([
            'index' => 'admin.roles.index',
            'create' => 'admin.roles.create',
            'store' => 'admin.roles.store',
            'edit' => 'admin.roles.edit',
            'update' => 'admin.roles.update',
            'destroy' => 'admin.roles.destroy',
        ]);
    });

    // Secure Evidence Download Endpoint
    Route::get('/incidents/{incident}/attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('incidents.attachments.download');
});
