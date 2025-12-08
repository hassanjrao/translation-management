<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Standardized JSON API responses.
 */
trait ApiResponseTrait
{
    protected function successResponse(mixed $data, string $message = 'Operation successful', int $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message, array $errors = [], int $code = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}

