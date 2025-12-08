<?php
namespace App\Infrastructure\Middleware;

use App\Application\Service\JWTService;
use App\Framework\HTTP\Request;
use App\Framework\HTTP\ErrorResponse;

class JWTMiddleware {
    public function __construct(private JWTService $jwtService) {}

    public function handle(Request $request): ?ErrorResponse {
        // Get token from header
        $authHeader = $request->getHeader('Authorization');
        if (!$authHeader) {
            return new ErrorResponse('Token not provided', 401);
        }

        // Extract token
        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            return new ErrorResponse('Invalid token format', 401);
        }

        $token = $parts[1];

        // Verify token
        try {
            $payload = $this->jwtService->verifyToken($token);
            
            if ($this->jwtService->isTokenExpired($payload)) {
                return new ErrorResponse('Token expired', 401);
            }

            // Attach user to request
            $request->setAttribute('user', $payload);
            return null; // Allow
        } catch (\Exception $e) {
            return new ErrorResponse('Invalid token: ' . $e->getMessage(), 401);
        }
    }
}

