<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\ReferenceDataService;

class CategoryObserver
{
    public function __construct(private readonly ReferenceDataService $referenceDataService) {}

    public function saved(Category $category): void
    {
        $this->referenceDataService->flushCache();
    }

    public function deleted(Category $category): void
    {
        $this->referenceDataService->flushCache();
    }
}
