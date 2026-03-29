<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logAction($model, 'created');
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        if ($model->wasChanged()) {
            $this->logAction($model, 'updated');
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logAction($model, 'deleted');
    }

    /**
     * Standardized internal method mapping polymorphic schema tracking smoothly into the audit log securely.
     */
    private function logAction(Model $model, string $action): void
    {
        /*
         * To prevent infinite loops or polluting logs, we ensure
         * we never recursively trace the AuditLog itself.
         */
        if ($model instanceof AuditLog) {
            return;
        }

        $changes = [];

        // Determine specific contextual changes mapping requested definitions
        if ($action === 'created') {
            $changes = $model->getAttributes();
            // Optional: Strip massive binary keys or hidden passwords if explicitly creating
            unset($changes['password']);
        } elseif ($action === 'updated') {
            foreach ($model->getChanges() as $key => $value) {
                // Ignore updating pure timestamp drifts unless other logical columns mutated
                if ($key !== 'updated_at') {
                    $original = $model->getOriginal($key);
                    $changes[$key] = [
                        'old' => $original,
                        'new' => $value,
                    ];
                }
            }
        } elseif ($action === 'deleted') {
            $changes = $model->getOriginal();
            unset($changes['password']);
        }

        // Only log if meaningful field changes were actually recorded inside the JSON tree
        if ($action === 'updated' && empty($changes)) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(), // Validates securely or natively leaves null if triggered by CLI
            'action' => $action,
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'changes' => rtrim(json_encode($changes), '}') === 'false' ? null : $changes,
        ]);
    }
}
