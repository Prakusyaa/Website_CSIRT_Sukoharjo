<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\IncidentResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — all responses wrapped in the standard ApiResponse envelope:
|
|  Success:   { "success": true, "data": ..., "message": "..." }
|  Paginated: { "success": true, "data": [...], "meta": {...}, "links": {...} }
|  Error:     { "success": false, "message": "...", "errors": {...} }
|--------------------------------------------------------------------------
*/

// Public — token issuance
Route::post('/login', [AuthController::class, 'login']);

// All remaining API routes require a valid Sanctum token
Route::middleware('auth:sanctum')->group(function () {

    // Auth management
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return ApiResponse::success(
            new UserResource($request->user()->load('role')),
            'Authenticated user retrieved.'
        );
    });

    /*
    |------------------------------------------------------------------
    | Incidents — accessible to all authenticated users (read) and
    | CSIRT+ for write operations (enforced in IncidentPolicy).
    |------------------------------------------------------------------
    */
    Route::prefix('incidents')->name('api.incidents.')->group(function () {

        Route::get('/', function () {
            Gate::authorize('viewAny', Report::class);

            $incidents = Report::query()
                ->select([
                    'id',
                    'subject',
                    'description',
                    'status',
                    'reporter_type',
                    'reporter_email',
                    'category_id',
                    'severity_id',
                    'reporter_id',
                    'assigned_to',
                    'created_by',
                    'created_at',
                    'updated_at',
                ])
                ->with([
                    'category:id,name',
                    'severity:id,name,level',
                    'reporter:id,name,username',
                    'assignedUser:id,name',
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return ApiResponse::paginated(IncidentResource::collection($incidents));
        })->name('index');

        Route::get('/{incident}', function (Report $incident) {
            Gate::authorize('view', $incident);

            $incident->load([
                'category:id,name',
                'severity:id,name,level',
                'reporter:id,name,username',
                'assignedUser:id,name',
                'creator:id,name',
                'attachments:id,report_id,file_name,file_type,file_size',
            ]);

            return ApiResponse::success(new IncidentResource($incident));
        })->name('show');

    });

    /*
    |------------------------------------------------------------------
    | Admin-only endpoints — validated server-side via isAdmin check.
    | The middleware alias 'role' is registered in bootstrap/app.php
    | but Sanctum routes use a separate guard; we check manually here.
    |------------------------------------------------------------------
    */
    Route::middleware('api.admin')->group(function () {

        // Users
        Route::get('/admin/users', function () {
            $users = User::query()
                ->select(['id', 'name', 'username', 'email', 'is_active', 'role_id', 'created_at', 'updated_at'])
                ->with('role:id,name,level')
                ->orderBy('id')
                ->paginate(20);

            return ApiResponse::paginated(UserResource::collection($users));
        })->name('api.admin.users.index');

        Route::get('/admin/users/{user}', function (User $user) {
            return ApiResponse::success(new UserResource($user->load('role:id,name,level')));
        })->name('api.admin.users.show');

        // Roles
        Route::get('/admin/roles', function () {
            $roles = Role::withCount('users')->orderBy('level')->get();

            return ApiResponse::success(RoleResource::collection($roles));
        })->name('api.admin.roles.index');

        Route::get('/admin/roles/{role}', function (Role $role) {
            $role->loadCount('users');

            return ApiResponse::success(new RoleResource($role));
        })->name('api.admin.roles.show');

        // Audit Logs (read-only via API)
        Route::get('/admin/audit-logs', function () {
            $logs = AuditLog::query()
                ->select(['id', 'action', 'table_name', 'record_id', 'changes', 'user_id', 'created_at'])
                ->with('user:id,name,email')
                ->orderBy('id', 'desc')
                ->paginate(20);

            return ApiResponse::paginated(AuditLogResource::collection($logs));
        })->name('api.admin.audit-logs.index');

    });

});
