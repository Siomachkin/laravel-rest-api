<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    public function handle(Request $request, Closure $next, string $limit = '60'): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }
        
        RateLimiter::hit($key);
        
        $response = $next($request);
        
        return $response->withHeaders([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => max(0, $limit - RateLimiter::attempts($key)),
            'X-RateLimit-Reset' => RateLimiter::availableIn($key),
        ]);
    }
    
    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(
            $request->method() . '|' . 
            $request->getHost() . '|' . 
            $request->ip() . '|' . 
            $request->userAgent()
        );
    }
}