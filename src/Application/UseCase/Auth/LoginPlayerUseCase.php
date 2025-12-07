<?php
namespace App\Application\UseCase\Auth;

use App\Application\DTO\LoginRequestDTO;
use App\Application\DTO\LoginResponseDTO;
use App\Application\Service\JWTService;
use App\Application\Service\PasswordService;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\Exception\InvalidCredentialsException;

class LoginPlayerUseCase {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordService $passwordService,
        private JWTService $jwtService
    ) {}

    public function execute(LoginRequestDTO $request): LoginResponseDTO {
        $email = new Email($request->email);
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new InvalidCredentialsException('Invalid email or password');
        }

        // Verify password
        if (!$user->verifyPassword($request->password, [$this->passwordService, 'verify'])) {
            throw new InvalidCredentialsException('Invalid email or password');
        }

        // Generate JWT tokens
        $accessToken = $this->jwtService->generateAccessToken([
            'userId' => $user->getId()->getValue(),
            'email' => $user->getEmail()->getValue(),
            'nickname' => $user->getNickname()
        ]);

        $refreshToken = $this->jwtService->generateRefreshToken([
            'userId' => $user->getId()->getValue()
        ]);

        return new LoginResponseDTO(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            user: [
                'id' => $user->getId()->getValue(),
                'email' => $user->getEmail()->getValue(),
                'nickname' => $user->getNickname()
            ]
        );
    }
}

