<?php
namespace App\Infrastructure\Controller;

use App\Application\UseCase\Auth\RegisterPlayerUseCase;
use App\Application\UseCase\Auth\LoginPlayerUseCase;
use App\Application\DTO\RegisterRequestDTO;
use App\Application\DTO\LoginRequestDTO;
use App\Framework\HTTP\JsonResponse;
use App\Framework\HTTP\Request;
use App\Framework\HTTP\ErrorResponse;

class AuthController {
    public function __construct(
        private RegisterPlayerUseCase $registerPlayerUseCase,
        private LoginPlayerUseCase $loginPlayerUseCase
    ) {}

    public function register(Request $request): JsonResponse|ErrorResponse {
        try {
            $body = $request->getBody();
            
            $dto = new RegisterRequestDTO(
                email: $body['email'] ?? '',
                password: $body['password'] ?? '',
                nickname: $body['nickname'] ?? null
            );

            $errors = $dto->validate();
            if (!empty($errors)) {
                return new ErrorResponse(implode(', ', $errors), 400);
            }

            $result = $this->registerPlayerUseCase->execute($dto);

            return new JsonResponse([
                'success' => true,
                'data' => $result->toArray()
            ], 201);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function login(Request $request): JsonResponse|ErrorResponse {
        try {
            $body = $request->getBody();
            
            $dto = new LoginRequestDTO(
                email: $body['email'] ?? '',
                password: $body['password'] ?? ''
            );

            $result = $this->loginPlayerUseCase->execute($dto);

            return new JsonResponse([
                'success' => true,
                'data' => $result->toArray()
            ]);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 401);
        }
    }
}

