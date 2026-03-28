<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A single consistent JSON envelope for every API response:
 *
 * Success (single):
 *   { "success": true, "data": { ... } }
 *
 * Success (collection / paginated):
 *   { "success": true, "data": [ ... ], "meta": { ... }, "links": { ... } }
 *
 * Error shape is provided by ApiResponse helper — this class handles
 * the per-resource wrapping only.
 */
abstract class BaseResource extends JsonResource
{
    /**
     * Wrap the resource in the standard success envelope.
     */
    public function with($request): array
    {
        return ['success' => true];
    }
}
