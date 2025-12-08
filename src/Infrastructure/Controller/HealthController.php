<?php
namespace App\Infrastructure\Controller;

use App\Framework\HTTP\JsonResponse;
use App\Framework\HTTP\Request;
use \PDO;
use \Redis;

class HealthController {
    public function __construct(
        private PDO $pdo,
        private Redis $redis
    ) {}

    public function check(Request $_request): JsonResponse {
        $status = [
            'status' => 'ok',
            'timestamp' => date('c'),
            'services' => []
        ];

        // Check PostgreSQL
        try {
            $this->pdo->query('SELECT 1');
            $status['services']['database'] = 'healthy';
        } catch (\Exception $e) {
            $status['services']['database'] = 'unhealthy';
            $status['status'] = 'degraded';
        }

        // Check Redis
        try {
            $this->redis->ping();
            $status['services']['cache'] = 'healthy';
        } catch (\Exception $e) {
            $status['services']['cache'] = 'unhealthy';
            $status['status'] = 'degraded';
        }

        $httpCode = $status['status'] === 'ok' ? 200 : 503;
        return new JsonResponse($status, $httpCode);
    }
}

