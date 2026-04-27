<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function errorResponse(string $message, int $status = 422, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'error' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    protected function paginatedResponse(string $message, mixed $resourceCollection): JsonResponse
    {
        // Handle if it's a JsonResource collection or raw paginator
        $data = $resourceCollection;
        if (method_exists($resourceCollection, 'response')) {
            $data = $resourceCollection->response()->getData(true);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data['data'] ?? $data,
            'meta' => $data['meta'] ?? $data['links'] ?? null,
        ]);
    }
}
