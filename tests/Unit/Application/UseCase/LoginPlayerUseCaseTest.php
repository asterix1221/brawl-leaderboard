<?php

namespace Tests\Unit\Application\UseCase;

use App\Application\DTO\LoginRequestDTO;
use App\Application\Service\JWTService;
use App\Application\Service\PasswordService;
use App\Application\UseCase\Auth\LoginPlayerUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\InvalidCredentialsException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoginPlayerUseCaseTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private PasswordService|MockObject $passwordService;
    private JWTService|MockObject $jwtService;
    private LoginPlayerUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordService = $this->createMock(PasswordService::class);
        $this->jwtService = $this->createMock(JWTService::class);

        $this->useCase = new LoginPlayerUseCase(
            $this->userRepository,
            $this->passwordService,
            $this->jwtService
        );
    }

    public function testSuccessfulLoginReturnsTokens(): void
    {
        $password = 'secret123';
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $user = new User(new Uuid('11111111-1111-1111-1111-111111111111'), new Email('test@example.com'), $hashedPassword, 'Tester');

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $this->passwordService
            ->expects($this->once())
            ->method('verify')
            ->with($password, $hashedPassword)
            ->willReturn(true);

        $this->jwtService
            ->expects($this->once())
            ->method('generateAccessToken')
            ->willReturn('access-token');

        $this->jwtService
            ->expects($this->once())
            ->method('generateRefreshToken')
            ->willReturn('refresh-token');

        $dto = new LoginRequestDTO(email: 'test@example.com', password: $password);
        $response = $this->useCase->execute($dto);

        $this->assertSame('access-token', $response->accessToken);
        $this->assertSame('refresh-token', $response->refreshToken);
        $this->assertSame('test@example.com', $response->user['email']);
        $this->assertSame('Tester', $response->user['nickname']);
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);

        $dto = new LoginRequestDTO(email: 'missing@example.com', password: 'password');

        $this->expectException(InvalidCredentialsException::class);
        $this->useCase->execute($dto);
    }

    public function testInvalidPasswordThrowsException(): void
    {
        $password = 'wrong';
        $hashedPassword = password_hash('correct', PASSWORD_BCRYPT);
        $user = new User(new Uuid('22222222-2222-2222-2222-222222222222'), new Email('user@example.com'), $hashedPassword, 'User');

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $this->passwordService
            ->expects($this->once())
            ->method('verify')
            ->with($password, $hashedPassword)
            ->willReturn(false);

        $dto = new LoginRequestDTO(email: 'user@example.com', password: $password);

        $this->expectException(InvalidCredentialsException::class);
        $this->useCase->execute($dto);
    }
}
