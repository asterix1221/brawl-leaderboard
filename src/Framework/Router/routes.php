<?php
// API Routes

// Health check
$router->get('/health', \App\Infrastructure\Controller\HealthController::class, 'check');

// Auth routes
$router->post('/auth/register', \App\Infrastructure\Controller\AuthController::class, 'register');
$router->post('/auth/login', \App\Infrastructure\Controller\AuthController::class, 'login');

// Leaderboard routes (public)
$router->get('/leaderboards/global', \App\Infrastructure\Controller\LeaderboardController::class, 'getGlobal');
$router->get('/players/search', \App\Infrastructure\Controller\LeaderboardController::class, 'search');

// Player routes
$router->get('/players/:playerId', \App\Infrastructure\Controller\PlayerController::class, 'getProfile', middleware: [\App\Infrastructure\Middleware\JWTMiddleware::class]);
$router->post('/players/link', \App\Infrastructure\Controller\PlayerController::class, 'link', middleware: [\App\Infrastructure\Middleware\JWTMiddleware::class]);

// Score routes
$router->post('/scores', \App\Infrastructure\Controller\ScoreController::class, 'upsert', middleware: [\App\Infrastructure\Middleware\JWTMiddleware::class]);
$router->put('/scores', \App\Infrastructure\Controller\ScoreController::class, 'upsert', middleware: [\App\Infrastructure\Middleware\JWTMiddleware::class]);

