<?php
namespace App\Framework\Router;

use App\Framework\Container\DIContainer;
use App\Framework\HTTP\Request;
use App\Framework\HTTP\ErrorResponse;

class Router {
    private array $routes = [];

    public function __construct(private DIContainer $container) {}

    public function get(string $path, string $controller, string $method, array $middleware = []): void {
        $this->routes['GET'][$path] = [
            'controller' => $controller,
            'method' => $method,
            'middleware' => $middleware
        ];
    }

    public function post(string $path, string $controller, string $method, array $middleware = []): void {
        $this->routes['POST'][$path] = [
            'controller' => $controller,
            'method' => $method,
            'middleware' => $middleware
        ];
    }

    public function put(string $path, string $controller, string $method, array $middleware = []): void {
        $this->routes['PUT'][$path] = [
            'controller' => $controller,
            'method' => $method,
            'middleware' => $middleware
        ];
    }

    public function delete(string $path, string $controller, string $method, array $middleware = []): void {
        $this->routes['DELETE'][$path] = [
            'controller' => $controller,
            'method' => $method,
            'middleware' => $middleware
        ];
    }

    public function dispatch(string $method, string $path, Request $request): string {
        // Try exact match first
        if (isset($this->routes[$method][$path])) {
            return $this->executeRoute($this->routes[$method][$path], $request, []);
        }

        // Try pattern matching (for routes with parameters like /players/:id)
        foreach ($this->routes[$method] ?? [] as $routePath => $route) {
            $pattern = $this->convertToPattern($routePath);
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                return $this->executeRoute($route, $request, $matches);
            }
        }

        return (string)new ErrorResponse('Route not found', 404);
    }

    private function convertToPattern(string $path): string {
        $pattern = preg_replace('/:(\w+)/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function executeRoute(array $route, Request $request, array $params): string {
        $middlewareError = $this->runMiddleware($route['middleware'] ?? [], $request);
        if ($middlewareError !== null) {
            return (string)$middlewareError;
        }

        $controllerClass = $route['controller'];
        $methodName = $route['method'];

        // Get controller from DI container
        $controller = $this->container->get($controllerClass);

        // Call method with request and params
        if (!empty($params)) {
            $result = $controller->$methodName($request, ...$params);
        } else {
            $result = $controller->$methodName($request);
        }

        return (string)$result;
    }

    private function runMiddleware(array $middlewareList, Request $request): ?ErrorResponse {
        foreach ($middlewareList as $middleware) {
            $resolved = $this->resolveMiddleware($middleware);

            if (is_callable($resolved)) {
                $result = $resolved($request);
            } elseif (method_exists($resolved, 'handle')) {
                $result = $resolved->handle($request);
            } else {
                throw new \RuntimeException('Invalid middleware provided');
            }

            if ($result instanceof ErrorResponse) {
                return $result;
            }
        }

        return null;
    }

    private function resolveMiddleware($middleware): mixed {
        if (is_string($middleware)) {
            if ($this->container->has($middleware)) {
                return $this->container->get($middleware);
            }

            if (class_exists($middleware)) {
                return new $middleware();
            }
        }

        return $middleware;
    }
}

