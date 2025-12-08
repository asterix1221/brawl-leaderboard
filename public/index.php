<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Framework\Container\DIContainer;
use App\Framework\Router\Router;
use App\Framework\HTTP\Request;
use App\Framework\Database\Connection;

// Add Redis class stub for IDE if not available
if (!class_exists('Redis')) {
    class Redis {
        public function connect($_host, $_port) { return true; }
        public function auth($_password) { return true; }
        public function get($_key) { return false; }
        public function set($_key, $_value, $_ttl = null) { return true; }
        public function del($_key) { return true; }
        public function exists($_key) { return false; }
        public function expire($_key, $_ttl) { return true; }
        public function incr($_key) { return 1; }
        public function flushAll() { return true; }
    }
}


// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create DI Container
$container = new DIContainer();

// ========== Database Connection ==========
$container->set('db', function() {
    return Connection::getInstance(
        $_ENV['DB_HOST'] ?? 'postgres',
        $_ENV['DB_NAME'] ?? 'brawl_stars',
        $_ENV['DB_USER'] ?? 'postgres',
        $_ENV['DB_PASSWORD'] ?? 'secret',
        (int)($_ENV['DB_PORT'] ?? 5432)
    );
});

// ========== Redis Connection ==========
$container->set('redis', function() {
    if (class_exists('Redis')) {
        $redis = new \Redis();
        $redis->connect($_ENV['REDIS_HOST'] ?? 'redis', (int)($_ENV['REDIS_PORT'] ?? 6379));
        if (!empty($_ENV['REDIS_PASSWORD'])) {
            $redis->auth($_ENV['REDIS_PASSWORD']);
        }
        return $redis;
    } else {
        // Fallback for local development without Redis extension
        throw new \RuntimeException('Redis extension not available. Please install Redis extension or run in Docker environment.');
    }
});

// ========== Application Services ==========
$container->set(\App\Application\Service\JWTService::class, function() {
    return new \App\Application\Service\JWTService($_ENV['JWT_SECRET'] ?? 'changeme');
});

$container->set(\App\Infrastructure\Middleware\JWTMiddleware::class, function($container) {
    return new \App\Infrastructure\Middleware\JWTMiddleware(
        $container->get(\App\Application\Service\JWTService::class)
    );
});

$container->set(\App\Application\Service\CacheService::class, function($container) {
    return new \App\Application\Service\CacheService($container->get('redis'));
});

$container->set(\App\Application\Service\PasswordService::class, function() {
    return new \App\Application\Service\PasswordService();
});

$container->set(\App\Application\Service\RateLimitService::class, function($container) {
    return new \App\Application\Service\RateLimitService($container->get('redis'));
});

$container->set(\App\Application\Service\BrawlStarsService::class, function() {
    return new \App\Application\Service\BrawlStarsService($_ENV['BRAWL_STARS_API_KEY'] ?? 'changeme');
});

// ========== Repository Interfaces ==========
$container->set(
    \App\Domain\Repository\PlayerRepositoryInterface::class,
    function($container) {
        return new \App\Infrastructure\Repository\PDOPlayerRepository($container->get('db'));
    }
);

$container->set(
    \App\Domain\Repository\UserRepositoryInterface::class,
    function($container) {
        return new \App\Infrastructure\Repository\PDOUserRepository($container->get('db'));
    }
);

$container->set(
    \App\Domain\Repository\ScoreRepositoryInterface::class,
    function($container) {
        return new \App\Infrastructure\Repository\PDOScoreRepository($container->get('db'));
    }
);

$container->set(
    \App\Domain\Repository\SeasonRepositoryInterface::class,
    function($container) {
        return new \App\Infrastructure\Repository\PDOSeasonRepository($container->get('db'));
    }
);

// ========== Use Cases ==========
$container->set(\App\Application\UseCase\Leaderboard\GetGlobalLeaderboardUseCase::class, function($container) {
    return new \App\Application\UseCase\Leaderboard\GetGlobalLeaderboardUseCase(
        $container->get(\App\Domain\Repository\PlayerRepositoryInterface::class),
        $container->get(\App\Domain\Repository\ScoreRepositoryInterface::class),
        $container->get(\App\Domain\Repository\SeasonRepositoryInterface::class),
        $container->get(\App\Application\Service\CacheService::class)
    );
});

$container->set(\App\Application\UseCase\Leaderboard\SearchPlayerUseCase::class, function($container) {
    return new \App\Application\UseCase\Leaderboard\SearchPlayerUseCase(
        $container->get(\App\Domain\Repository\PlayerRepositoryInterface::class)
    );
});

$container->set(\App\Application\UseCase\Auth\RegisterPlayerUseCase::class, function($container) {
    return new \App\Application\UseCase\Auth\RegisterPlayerUseCase(
        $container->get(\App\Domain\Repository\UserRepositoryInterface::class),
        $container->get(\App\Application\Service\PasswordService::class),
        $container->get(\App\Application\Service\JWTService::class)
    );
});

$container->set(\App\Application\UseCase\Auth\LoginPlayerUseCase::class, function($container) {
    return new \App\Application\UseCase\Auth\LoginPlayerUseCase(
        $container->get(\App\Domain\Repository\UserRepositoryInterface::class),
        $container->get(\App\Application\Service\PasswordService::class),
        $container->get(\App\Application\Service\JWTService::class)
    );
});

$container->set(\App\Application\UseCase\Player\GetPlayerProfileUseCase::class, function($container) {
    return new \App\Application\UseCase\Player\GetPlayerProfileUseCase(
        $container->get(\App\Domain\Repository\PlayerRepositoryInterface::class)
    );
});

$container->set(\App\Application\UseCase\Player\LinkBrawlStarsPlayerUseCase::class, function($container) {
    return new \App\Application\UseCase\Player\LinkBrawlStarsPlayerUseCase(
        $container->get(\App\Domain\Repository\PlayerRepositoryInterface::class),
        $container->get(\App\Domain\Repository\UserRepositoryInterface::class),
        $container->get(\App\Application\Service\BrawlStarsService::class)
    );
});

$container->set(\App\Application\UseCase\Score\UpsertPlayerScoreUseCase::class, function($container) {
    return new \App\Application\UseCase\Score\UpsertPlayerScoreUseCase(
        $container->get(\App\Domain\Repository\ScoreRepositoryInterface::class),
        $container->get(\App\Domain\Repository\PlayerRepositoryInterface::class),
        $container->get(\App\Domain\Repository\SeasonRepositoryInterface::class),
        $container->get(\App\Application\Service\CacheService::class)
    );
});

// ========== Controllers ==========
$container->set(\App\Infrastructure\Controller\HealthController::class, function($container) {
    return new \App\Infrastructure\Controller\HealthController(
        $container->get('db'),
        $container->get('redis')
    );
});

$container->set(\App\Infrastructure\Controller\AuthController::class, function($container) {
    return new \App\Infrastructure\Controller\AuthController(
        $container->get(\App\Application\UseCase\Auth\RegisterPlayerUseCase::class),
        $container->get(\App\Application\UseCase\Auth\LoginPlayerUseCase::class)
    );
});

$container->set(\App\Infrastructure\Controller\LeaderboardController::class, function($container) {
    return new \App\Infrastructure\Controller\LeaderboardController(
        $container->get(\App\Application\UseCase\Leaderboard\GetGlobalLeaderboardUseCase::class),
        $container->get(\App\Application\UseCase\Leaderboard\SearchPlayerUseCase::class)
    );
});

$container->set(\App\Infrastructure\Controller\PlayerController::class, function($container) {
    return new \App\Infrastructure\Controller\PlayerController(
        $container->get(\App\Application\UseCase\Player\GetPlayerProfileUseCase::class),
        $container->get(\App\Application\UseCase\Player\LinkBrawlStarsPlayerUseCase::class)
    );
});

$container->set(\App\Infrastructure\Controller\ScoreController::class, function($container) {
    return new \App\Infrastructure\Controller\ScoreController(
        $container->get(\App\Application\UseCase\Score\UpsertPlayerScoreUseCase::class)
    );
});



// ========== Router ==========
$router = new Router($container);

// Load routes
require_once __DIR__ . '/../src/Framework/Router/routes.php';

// ========== Request Handling ==========
try {
    $request = Request::fromGlobals();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    
    // Remove /api prefix if present
    $path = str_replace('/api', '', $path);
    if (empty($path)) {
        $path = '/';
    }

    // Handle CORS
    $corsMiddleware = new \App\Infrastructure\Middleware\CorsMiddleware(
        $_ENV['APP_CORS_ORIGIN'] ?? '*'
    );
    $corsResponse = $corsMiddleware->handle($request);
    if ($corsResponse !== null) {
        exit;
    }

    // Apply rate limiting
    $rateLimitMiddleware = new \App\Infrastructure\Middleware\RateLimitMiddleware(
        $container->get(\App\Application\Service\RateLimitService::class)
    );
    $rateLimitError = $rateLimitMiddleware->handle($request);
    if ($rateLimitError !== null) {
        echo (string)$rateLimitError;
        exit;
    }

    // Dispatch request
    $response = $router->dispatch($method, $path, $request);
    
    // Add CORS headers to response
    if ($response instanceof \App\Framework\HTTP\JsonResponse || $response instanceof \App\Framework\HTTP\ErrorResponse) {
        $corsMiddleware->addHeaders($response);
    }
    
    echo $response;
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
