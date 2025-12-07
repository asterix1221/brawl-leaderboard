<?php
namespace Tests\Unit\Application\UseCase\Auth;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Auth\RegisterPlayerUseCase;
use App\Application\DTO\RegisterRequestDTO;
use App\Application\DTO\LoginResponseDTO;
use App\Application\Service\JWTService;
use App\Application\Service\PasswordService;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\Exception\DuplicateEmailException;

class RegisterPlayerUseCaseTest extends TestCase {
    private RegisterPlayerUseCase $useCase;
    private $userRepositoryMock;
    private $passwordServiceMock;
    private $jwtServiceMock;

    protected function setUp(): void {
        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->passwordServiceMock = $this->createMock(PasswordService::class);
        $this->jwtServiceMock = $this->createMock(JWTService::class);

        $this->useCase = new RegisterPlayerUseCase(
            $this->userRepositoryMock,
            $this->passwordServiceMock,
            $this->jwtServiceMock
        );
    }

    public function testRegisterNewPlayerSuccessfully(): void {
        // Arrange
        $request = new RegisterRequestDTO(
            email: 'test@example.com',
            password: 'StrongPass123!',
            nickname: 'TestPlayer'
        );

        $emailValueObject = new Email('test@example.com');
        $hashedPassword = 'hashed_password_here';
        $jwtToken = 'jwt_token_here';
        
        $user = new User(
            id: $this->createMock(\App\Domain\ValueObject\Uuid::class),
            email: $emailValueObject,
            passwordHash: $hashedPassword,
            nickname: 'TestPlayer'
        );

        // Mock expectations
        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->equalTo($emailValueObject))
            ->willReturn(null);

        $this->passwordServiceMock
            ->expects($this->once())
            ->method('validatePasswordStrength')
            ->with('StrongPass123!')
            ->willReturn([]);

        $this->passwordServiceMock
            ->expects($this->once())
            ->method('hash')
            ->with('StrongPass123!')
            ->willReturn($hashedPassword);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($userToSave) use ($emailValueObject, $hashedPassword) {
                return $userToSave->getEmail()->getValue() === 'test@example.com' &&
                       $userToSave->getNickname() === 'TestPlayer';
            }));

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
            ->willReturn('refresh_token_here');

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(LoginResponseDTO::class, $result);
        $this->assertEquals($jwtToken, $result->accessToken);
        $this->assertEquals('test@example.com', $result->user['email']);
        $this->assertEquals('TestPlayer', $result->user['nickname']);
    }

    public function testRegisterWithDuplicateEmailThrowsException(): void {
        // Arrange
        $request = new RegisterRequestDTO(
            email: 'existing@example.com',
            password: 'StrongPass123!',
            nickname: 'TestPlayer'
        );

        $existingUser = new User(
            id: $this->createMock(\App\Domain\ValueObject\Uuid::class),
            email: new Email('existing@example.com'),
            passwordHash: 'hashed_password',
            nickname: 'ExistingPlayer'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->equalTo(new Email('existing@example.com')))
            ->willReturn($existingUser);

        $this->passwordServiceMock
            ->expects($this->never())
            ->method('validatePasswordStrength');

        $this->userRepositoryMock
            ->expects($this->never())
            ->method('save');

        $this->jwtServiceMock
            ->expects($this->never())
            ->method('generateAccessToken');

        // Act & Assert
        $this->expectException(DuplicateEmailException::class);
        $this->expectExceptionMessage('Email already registered');

        $this->useCase->execute($request);
    }

    public function testRegisterWithWeakPasswordValidatesAndThrowsException(): void {
        // Arrange
        $request = new RegisterRequestDTO(
            email: 'test@example.com',
            password: 'weak',
            nickname: 'TestPlayer'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->equalTo(new Email('test@example.com')))
            ->willReturn(null);

        $this->passwordServiceMock
            ->expects($this->once())
            ->method('validatePasswordStrength')
            ->with('weak')
            ->willReturn(['Password too weak']);

        $this->passwordServiceMock
            ->expects($this->never())
            ->method('hash');

        $this->userRepositoryMock
            ->expects($this->never())
            ->method('save');

        $this->jwtServiceMock
            ->expects($this->never())
            ->method('generateAccessToken');

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password validation failed: Password too weak');

        $this->useCase->execute($request);
    }

    public function testRegisterWithoutNicknameUsesNull(): void {
        // Arrange
        $request = new RegisterRequestDTO(
            email: 'test@example.com',
            password: 'StrongPass123!',
            nickname: null
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->equalTo(new Email('test@example.com')))
            ->willReturn(null);

        $this->passwordServiceMock
            ->expects($this->once())
            ->method('validatePasswordStrength')
            ->with('StrongPass123!')
            ->willReturn([]);

        $this->passwordServiceMock
            ->expects($this->once())
            ->method('hash')
            ->with('StrongPass123!')
            ->willReturn('hashed_password');

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($userToSave) {
                return $userToSave->getNickname() === null || $userToSave->getNickname() === '';
            }));

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
        $this->assertEquals('', $result->user['nickname']);
    }

    public function testRegisterWithEmptyNicknameUsesNull(): void {
        // Arrange
        $request = new RegisterRequestDTO(
            email: 'test@example.com',
            password: 'StrongPass123!',
            nickname: ''
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->equalTo(new Email('test@example.com')))
            ->willReturn(null);

        $this->passwordServiceMock
            ->expects($this->once())
            ->method('validatePasswordStrength')
            ->with('StrongPass123!')
            ->willReturn([]);

        $this->passwordServiceMock
            ->expects($this->once())
            ->method('hash')
            ->with('StrongPass123!')
            ->willReturn('hashed_password');

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($userToSave) {
                return $userToSave->getNickname() === null || $userToSave->getNickname() === '';
            }));

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
        $this->assertEquals('', $result->user['nickname']);
    }
}
