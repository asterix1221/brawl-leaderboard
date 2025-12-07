<?php
namespace App\Application\UseCase\Auth;

use App\Application\DTO\RegisterRequestDTO;
use App\Application\DTO\LoginResponseDTO;
use App\Application\Service\JWTService;
use App\Application\Service\PasswordService;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Uuid;
use App\Domain\Exception\DuplicateEmailException;

class RegisterPlayerUseCase {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordService $passwordService,
        private JWTService $jwtService
    ) {}

    public function execute(RegisterRequestDTO $request): LoginResponseDTO {
        $email = new Email($request->email);
        
        // Check if email already exists
        if ($this->userRepository->findByEmail($email) !== null) {
            throw new DuplicateEmailException('Email already registered');
        }

        // Validate password strength
        $passwordErrors = $this->passwordService->validatePasswordStrength($request->password);
        if (!empty($passwordErrors)) {
            throw new \InvalidArgumentException('Password validation failed: ' . implode(', ', $passwordErrors));
        }

        // Create user entity
        $user = new User(
            id: Uuid::generate(),
            email: $email,
            passwordHash: $this->passwordService->hash($request->password),
            nickname: $request->nickname
        );

        // Save to repository
        $this->userRepository->save($user);

        // Generate JWT token
        $token = $this->jwtService->generateAccessToken([
            'userId' => $user->getId()->getValue(),
            'email' => $user->getEmail()->getValue(),
            'nickname' => $user->getNickname()
        ]);

        $refreshToken = $this->jwtService->generateRefreshToken([
            'userId' => $user->getId()->getValue()
        ]);

        return new LoginResponseDTO(
            accessToken: $token,
            refreshToken: $refreshToken,
            user: [
                'id' => $user->getId()->getValue(),
                'email' => $user->getEmail()->getValue(),
                'nickname' => $user->getNickname()
            ]
        );
    }
}

