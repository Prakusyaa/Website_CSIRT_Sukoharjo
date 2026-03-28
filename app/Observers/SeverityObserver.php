<?php

namespace App\Observers;

use App\Models\Severity;
use App\Services\ReferenceDataService;

class SeverityObserver
{
    public function __construct(private readonly ReferenceDataService $referenceDataService) {}

    public function saved(Severity $severity): void
    {
        $this->referenceDataService->flushCache();
    }

    public function deleted(Severity $severity): void
    {
        $this->referenceDataService->flushCache();
    }
}
