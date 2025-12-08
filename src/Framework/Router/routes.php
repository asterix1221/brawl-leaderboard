<?php
// API Routes

// Health check
$router->get('/api/health', \App\Infrastructure\Controller\HealthController::class, 'check');

// Auth routes
$router->post('/api/auth/register', \App\Infrastructure\Controller\AuthController::class, 'register');
$router->post('/api/auth/login', \App\Infrastructure\Controller\AuthController::class, 'login');

// Leaderboard routes (public)
$router->get('/api/leaderboards/global', \App\Infrastructure\Controller\LeaderboardController::class, 'getGlobal');
$router->get('/api/players/search', \App\Infrastructure\Controller\LeaderboardController::class, 'search');

// Player routes
$router->get('/api/players/:playerId', \App\Infrastructure\Controller\PlayerController::class, 'getProfile', middleware: [\App\Infrastructure\Middleware\JWTMiddleware::class]);
$router->post('/api/players/link', \App\Infrastructure\Controller\PlayerController::class, 'link', middleware: [\App\Infrastructure\Middleware\JWTMiddleware::class]);

// Score routes
$router->post('/api/scores', \App\Infrastructure\Controller\ScoreController::class, 'upsert', middleware: [\App\Infrastructure\Middleware\JWTMiddleware::class]);
$router->put('/api/scores', \App\Infrastructure\Controller\ScoreController::class, 'upsert', middleware: [\App\Infrastructure\Middleware\JWTMiddleware::class]);

