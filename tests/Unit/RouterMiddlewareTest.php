<?php

namespace Tests\Unit;

use App\Application\Service\JWTService;
use App\Framework\Container\DIContainer;
use App\Framework\HTTP\ErrorResponse;
use App\Framework\HTTP\JsonResponse;
use App\Framework\HTTP\Request;
use App\Framework\Router\Router;
use App\Infrastructure\Middleware\JWTMiddleware;
use App\Infrastructure\Middleware\RateLimitMiddleware;
use PHPUnit\Framework\TestCase;

class RouterMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['REQUEST_URI'] = '/api/scores';
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    public function testProtectedRouteBlocksRequestWithoutToken(): void
    {
        $container = new DIContainer();
        $container->set(JWTService::class, fn () => new JWTService('secret'));
        $container->set(JWTMiddleware::class, fn ($c) => new JWTMiddleware($c->get(JWTService::class)));
        $container->set(DummyController::class, fn () => new DummyController());

        $router = new Router($container);
        $router->post('/scores', DummyController::class, 'handle', middleware: [JWTMiddleware::class]);

        $request = Request::fromGlobals();
        $response = $router->dispatch('POST', '/scores', $request);

        $this->assertJson($response);
        $data = json_decode($response, true);

        $this->assertFalse($data['success']);
        $this->assertSame(401, $data['code']);
    }

    public function testRateLimitingRunsAfterRouteMiddleware(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/protected';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $container = new DIContainer();
        $rateLimitMiddleware = new InspectingRateLimitMiddleware();

        $container->set(RateLimitMiddleware::class, fn () => $rateLimitMiddleware);
        $container->set(DummyController::class, fn () => new DummyController());

        $router = new Router($container);
        $router->get('/protected', DummyController::class, 'handle', middleware: [AttributeSettingMiddleware::class]);

        $request = Request::fromGlobals();
        $response = $router->dispatch('GET', '/protected', $request);

        $this->assertJson($response);
        $data = json_decode($response, true);

        $this->assertTrue($data['success']);
        $this->assertSame(['userId' => 123], $rateLimitMiddleware->capturedUser);
    }
}

class DummyController
{
    public function handle(Request $request): JsonResponse
    {
        return new JsonResponse(['ok' => true]);
    }
}

class AttributeSettingMiddleware
{
    public function handle(Request $request): ?ErrorResponse
    {
        $request->setAttribute('user', ['userId' => 123]);
        return null;
    }
}

class InspectingRateLimitMiddleware extends RateLimitMiddleware
{
    public ?array $capturedUser = null;

    public function __construct() {}

    public function handle(Request $request): ?ErrorResponse
    {
        $this->capturedUser = $request->getAttribute('user');

        if ($this->capturedUser === null) {
            return new ErrorResponse('User attribute missing', 500);
        }

        return null;
    }
}
