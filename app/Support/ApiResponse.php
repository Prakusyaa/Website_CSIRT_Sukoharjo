<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Centralised factory for all API JSON responses.
 *
 * Usage:
 *   return ApiResponse::success($resource);
 *   return ApiResponse::success($collection, 'Incidents retrieved.');
 *   return ApiResponse::error('Not found.', 404);
 *   return ApiResponse::validationError($validator->errors());
 */
class ApiResponse
{
    /**
     * Standard success response.
     *
     * @param  mixed  $data  Already-transformed resource / array / null
     */
    public static function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        $body = ['success' => true];

        if ($message !== '') {
            $body['message'] = $message;
        }

        if ($data !== null) {
            $body['data'] = $data;
        }

        return response()->json($body, $status);
    }

    /**
     * Standard error response.
     */
    public static function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    /**
     * Standard validation failure (422) with field-level errors.
     */
    public static function validationError(mixed $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors'  => $errors,
        ], 422);
    }

    /**
     * Paginated resource collection in the standard envelope.
     * Pass the result of ResourceClass::collection($paginator) here.
     */
    public static function paginated(mixed $resourceCollection): JsonResponse
    {
        // ResourceCollection's toResponse() already builds meta + links from the paginator
        return $resourceCollection->response()->setStatusCode(200);
    }
}
