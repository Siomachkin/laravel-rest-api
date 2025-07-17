<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponseTrait
{
    protected function successResponse(
        $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $headers = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode, $headers);
    }

    protected function errorResponse(
        string $message = 'Error',
        int $statusCode = 400,
        array $errors = [],
        array $headers = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode, $headers);
    }

    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        string $message = 'Success'
    ): JsonResponse {
        return $this->successResponse(
            $paginator->items(),
            $message,
            200,
            []
        )->withHeaders([
            'X-Pagination-Current-Page' => $paginator->currentPage(),
            'X-Pagination-Per-Page' => $paginator->perPage(),
            'X-Pagination-Total' => $paginator->total(),
            'X-Pagination-Last-Page' => $paginator->lastPage(),
        ]);
    }

    protected function createdResponse(
        $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    protected function noContentResponse(
        string $message = 'No content'
    ): JsonResponse {
        return $this->successResponse(null, $message, 204);
    }

    protected function notFoundResponse(
        string $message = 'Resource not found'
    ): JsonResponse {
        return $this->errorResponse($message, 404);
    }

    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    protected function forbiddenResponse(
        string $message = 'Access forbidden'
    ): JsonResponse {
        return $this->errorResponse($message, 403);
    }

    protected function unauthorizedResponse(
        string $message = 'Authentication required'
    ): JsonResponse {
        return $this->errorResponse($message, 401);
    }

    protected function serverErrorResponse(
        string $message = 'Internal server error'
    ): JsonResponse {
        return $this->errorResponse($message, 500);
    }
}