<?php
namespace App\Infrastructure\Middleware;

use App\Application\Service\RateLimitService;
use App\Framework\HTTP\Request;
use App\Framework\HTTP\ErrorResponse;

class RateLimitMiddleware {
    public function __construct(private RateLimitService $rateLimitService) {}

    public function handle(Request $request): ?ErrorResponse {
        // Get client identifier (IP address or user ID)
        $identifier = $this->getClientIdentifier($request);

        if (!$this->rateLimitService->isAllowed($identifier)) {
            return new ErrorResponse('Too many requests', 429);
        }

        $this->rateLimitService->increment($identifier);
        return null; // Allow
    }

    private function getClientIdentifier(Request $request): string {
        // Try to get user ID from request (if authenticated)
        $user = $request->getAttribute('user');
        if ($user && isset($user['userId'])) {
            return 'user:' . $user['userId'];
        }

        // Fallback to IP address
        return 'ip:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
}

