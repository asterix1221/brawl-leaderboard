<?php
namespace App\Infrastructure\Middleware;

use App\Framework\HTTP\Request;
use App\Framework\HTTP\Response;

class CorsMiddleware {
    private string $allowedOrigin;

    public function __construct(string $allowedOrigin = '*') {
        $this->allowedOrigin = $allowedOrigin;
    }

    public function handle(Request $request): ?Response {
        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response(204);
            $response->setHeader('Access-Control-Allow-Origin', $this->allowedOrigin);
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            $response->setHeader('Access-Control-Max-Age', '3600');
            $response->send();
            exit;
        }

        return null; // Continue to next middleware
    }

    public function addHeaders(Response $response): void {
        $response->setHeader('Access-Control-Allow-Origin', $this->allowedOrigin);
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}

