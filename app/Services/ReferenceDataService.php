<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Role;
use App\Models\Severity;
use Illuminate\Support\Facades\Cache;

/**
 * Centralized service to fetch and heavily cache static/reference tables.
 * This completely eradicates redundant database queries (N+1 lookups) during
 * form creations, rendering dropdowns, and basic relations.
 */
class ReferenceDataService
{
    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Retrieve all categories, leveraging cache.
     */
    public function getCategories()
    {
        return Cache::remember('reference.categories', self::CACHE_TTL, function () {
            return Category::select('id', 'name')->orderBy('name')->get();
        });
    }

    /**
     * Retrieve all severities ordered by threat level, leveraging cache.
     */
    public function getSeverities()
    {
        return Cache::remember('reference.severities', self::CACHE_TTL, function () {
            return Severity::select('id', 'name', 'level')->orderBy('level')->get();
        });
    }

    /**
     * Retrieve all system roles, leveraging cache.
     *
     * @return list<array{id: int, name: string, level: int}>
     */
    public function getRoles(): array
    {
        return Cache::remember('reference.roles.v2', self::CACHE_TTL, function () {
            return Role::query()
                ->orderBy('level')
                ->get(['id', 'name', 'level'])
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'level' => $role->level,
                ])
                ->all();
        });
    }

    /**
     * Flush all reference caches instantly.
     * Triggered by observers when an admin edits reference data.
     */
    public function flushCache(): void
    {
        Cache::forget('reference.categories');
        Cache::forget('reference.severities');
        Cache::forget('reference.roles');
        Cache::forget('reference.roles.v2');
    }
}
