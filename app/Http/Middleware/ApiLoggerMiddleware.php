<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'headers' => $request->headers->all(),
            'body' => $request->getContent(),
        ]);

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;

        Log::info('API Response', [
            'status' => $response->getStatusCode(),
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'response_size' => strlen($response->getContent()) . ' bytes',
        ]);

        return $response;
    }
}
