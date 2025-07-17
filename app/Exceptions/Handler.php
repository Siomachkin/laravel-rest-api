<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception): Response|JsonResponse
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    protected function handleApiException(Request $request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
            ], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
            ], 405);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Access forbidden',
            ], 403);
        }

        if ($exception instanceof HttpException) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'HTTP error',
            ], $exception->getStatusCode());
        }

        // Log the exception for debugging
        report($exception);

        return response()->json([
            'success' => false,
            'message' => config('app.debug') ? $exception->getMessage() : 'Internal server error',
        ], 500);
    }
}