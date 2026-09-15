<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitExamActions
{
    /**
     * Handle an incoming request for exam-related actions with custom rate limiting.
     */
    public function handle(Request $request, Closure $next, string $limit = '60'): Response
    {
        $key = $this->getKey($request);
        $maxAttempts = (int) $limit;
        
        // Check if rate limit is exceeded
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak request. Coba lagi dalam ' . $seconds . ' detik.',
                'retry_after' => $seconds,
            ], 429);
        }
        
        // Increment attempts
        RateLimiter::hit($key, 60); // Decay after 60 seconds
        
        return $next($request);
    }
    
    /**
     * Generate rate limit key based on user and route
     */
    protected function getKey(Request $request): string
    {
        $userId = $request->user()?->id ?? $request->ip();
        $route = $request->route()?->getName() ?? $request->path();
        
        return 'exam_action:' . $userId . ':' . $route;
    }
}
