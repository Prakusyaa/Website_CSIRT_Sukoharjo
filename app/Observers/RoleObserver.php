<?php

namespace App\Observers;

use App\Models\Role;
use App\Services\ReferenceDataService;

class RoleObserver
{
    public function __construct(private readonly ReferenceDataService $referenceDataService) {}

    public function saved(Role $role): void
    {
        $this->referenceDataService->flushCache();
    }

    public function deleted(Role $role): void
    {
        $this->referenceDataService->flushCache();
    }
}
