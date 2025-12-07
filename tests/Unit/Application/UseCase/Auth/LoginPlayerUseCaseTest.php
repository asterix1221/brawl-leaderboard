<?php
namespace Tests\Unit\Application\UseCase\Auth;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Auth\LoginPlayerUseCase;
use App\Application\DTO\LoginRequestDTO;
use App\Application\DTO\LoginResponseDTO;
use App\Application\Service\JWTService;
use App\Application\Service\PasswordService;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use App\Domain\Exception\InvalidCredentialsException;

class LoginPlayerUseCaseTest extends TestCase {
    private LoginPlayerUseCase $useCase;
    private $userRepositoryMock;
    private $passwordServiceMock;
    private $jwtServiceMock;

    protected function setUp(): void {
        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->passwordServiceMock = $this->createMock(PasswordService::class);
        $this->jwtServiceMock = $this->createMock(JWTService::class);

        $this->useCase = new LoginPlayerUseCase(
            $this->userRepositoryMock,
            $this->passwordServiceMock,
            $this->jwtServiceMock
        );
    }

    public function testLoginWithValidCredentials(): void {
        // Arrange
        $request = new LoginRequestDTO(
            email: 'test@example.com',
            password: 'StrongPass123!'
        );

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($this->createMock(\App\Domain\ValueObject\Uuid::class));
        $user->method('getEmail')->willReturn(new Email('test@example.com'));
        $user->method('getNickname')->willReturn('TestPlayer');

        $jwtToken = 'jwt_token_here';

        // Mock expectations
        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(function($email) {
                return $email instanceof Email && $email->getValue() === 'test@example.com';
            }))
            ->willReturn($user);

        $user
            ->expects($this->once())
            ->method('verifyPassword')
            ->with('StrongPass123!', [$this->passwordServiceMock, 'verify'])
            ->willReturn(true);

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('generateAccessToken')
            ->with($this->callback(function($payload) {
                return isset($payload['userId']) && 
                       isset($payload['email']) && 
                       $payload['email'] === 'test@example.com';
            }))
            ->willReturn($jwtToken);

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('generateRefreshToken')
            ->with($this->callback(function($payload) {
                return isset($payload['userId']);
            }))
            ->willReturn('refresh_token');

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(LoginResponseDTO::class, $result);
        $this->assertEquals($jwtToken, $result->accessToken);
        $this->assertEquals('test@example.com', $result->user['email']);
        $this->assertEquals('TestPlayer', $result->user['nickname']);
    }

    public function testLoginWithNonExistentEmailThrowsException(): void {
        // Arrange
        $request = new LoginRequestDTO(
            email: 'nonexistent@example.com',
            password: 'StrongPass123!'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(function($email) {
                return $email instanceof Email && $email->getValue() === 'nonexistent@example.com';
            }))
            ->willReturn(null);

        $this->passwordServiceMock
            ->expects($this->never())
            ->method('verify');

        $this->jwtServiceMock
            ->expects($this->never())
            ->method('generateAccessToken');

        // Act & Assert
        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid email or password');

        $this->useCase->execute($request);
    }

    public function testLoginWithWrongPasswordThrowsException(): void {
        // Arrange
        $request = new LoginRequestDTO(
            email: 'test@example.com',
            password: 'WrongPassword!'
        );

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($this->createMock(\App\Domain\ValueObject\Uuid::class));
        $user->method('getEmail')->willReturn(new Email('test@example.com'));
        $user->method('getNickname')->willReturn('TestPlayer');

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(function($email) {
                return $email instanceof Email && $email->getValue() === 'test@example.com';
            }))
            ->willReturn($user);

        $user
            ->expects($this->once())
            ->method('verifyPassword')
            ->with('WrongPassword!', [$this->passwordServiceMock, 'verify'])
            ->willReturn(false);

        $this->jwtServiceMock
            ->expects($this->never())
            ->method('generateAccessToken');

        // Act & Assert
        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid email or password');

        $this->useCase->execute($request);
    }

    public function testLoginWithUserWithNullNickname(): void {
        // Arrange
        $request = new LoginRequestDTO(
            email: 'test@example.com',
            password: 'StrongPass123!'
        );

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($this->createMock(\App\Domain\ValueObject\Uuid::class));
        $user->method('getEmail')->willReturn(new Email('test@example.com'));
        $user->method('getNickname')->willReturn(null);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(function($email) {
                return $email instanceof Email && $email->getValue() === 'test@example.com';
            }))
            ->willReturn($user);

        $user
            ->expects($this->once())
            ->method('verifyPassword')
            ->with('StrongPass123!', [$this->passwordServiceMock, 'verify'])
            ->willReturn(true);

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('generateAccessToken')
            ->willReturn('jwt_token');

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('generateRefreshToken')
            ->willReturn('refresh_token');

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(LoginResponseDTO::class, $result);
        $this->assertEquals('test@example.com', $result->user['email']);
        $this->assertNull($result->user['nickname']);
    }

    public function testLoginWithUserWithEmptyNickname(): void {
        // Arrange
        $request = new LoginRequestDTO(
            email: 'test@example.com',
            password: 'StrongPass123!'
        );

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($this->createMock(\App\Domain\ValueObject\Uuid::class));
        $user->method('getEmail')->willReturn(new Email('test@example.com'));
        $user->method('getNickname')->willReturn('');

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(function($email) {
                return $email instanceof Email && $email->getValue() === 'test@example.com';
            }))
            ->willReturn($user);

        $user
            ->expects($this->once())
            ->method('verifyPassword')
            ->with('StrongPass123!', [$this->passwordServiceMock, 'verify'])
            ->willReturn(true);

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('generateAccessToken')
            ->willReturn('jwt_token');

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('generateRefreshToken')
            ->willReturn('refresh_token');

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(LoginResponseDTO::class, $result);
        $this->assertEquals('test@example.com', $result->user['email']);
        $this->assertEquals('', $result->user['nickname']);
    }

    public function testLoginWithEmptyEmailThrowsException(): void {
        // Arrange
        $request = new LoginRequestDTO(
            email: '',
            password: 'StrongPass123!'
        );

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format: ');

        $this->useCase->execute($request);
    }

    public function testLoginWithEmptyPasswordThrowsException(): void {
        // Arrange
        $request = new LoginRequestDTO(
            email: 'test@example.com',
            password: ''
        );

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($this->createMock(\App\Domain\ValueObject\Uuid::class));
        $user->method('getEmail')->willReturn(new Email('test@example.com'));
        $user->method('getNickname')->willReturn('TestPlayer');

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(function($email) {
                return $email instanceof Email && $email->getValue() === 'test@example.com';
            }))
            ->willReturn($user);

        $user
            ->expects($this->once())
            ->method('verifyPassword')
            ->with('', [$this->passwordServiceMock, 'verify'])
            ->willReturn(false);

        $this->jwtServiceMock
            ->expects($this->never())
            ->method('generateAccessToken');

        // Act & Assert
        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid email or password');

        $this->useCase->execute($request);
    }
}
