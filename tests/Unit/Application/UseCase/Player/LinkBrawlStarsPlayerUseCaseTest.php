<?php
namespace Tests\Unit\Application\UseCase\Player;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Player\LinkBrawlStarsPlayerUseCase;
use App\Application\Service\BrawlStarsService;
use App\Domain\Entity\Player;
use App\Domain\Entity\User;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;
use App\Domain\ValueObject\Uuid;
use App\Domain\Exception\PlayerNotFoundException;

class LinkBrawlStarsPlayerUseCaseTest extends TestCase {
    private LinkBrawlStarsPlayerUseCase $useCase;
    private $playerRepositoryMock;
    private $userRepositoryMock;
    private $brawlStarsServiceMock;

    protected function setUp(): void {
        $this->playerRepositoryMock = $this->createMock(PlayerRepositoryInterface::class);
        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->brawlStarsServiceMock = $this->createMock(BrawlStarsService::class);

        $this->useCase = new LinkBrawlStarsPlayerUseCase(
            $this->playerRepositoryMock,
            $this->userRepositoryMock,
            $this->brawlStarsServiceMock
        );
    }

    public function testLinkPlayerSuccessfully(): void {
        // Arrange
        $userId = Uuid::generate();
        $brawlStarsPlayerId = 'player456';
        
        $playerData = [
            'name' => 'TestPlayer',
            'trophies' => 1500,
            'club' => ['tag' => 'GLOBAL']
        ];

        $player = new Player(
            new PlayerId($brawlStarsPlayerId),
            'TestPlayer',
            new Trophy(1500),
            'GLOBAL'
        );

        $user = new User(
            id: $userId,
            email: new \App\Domain\ValueObject\Email('test@example.com'),
            passwordHash: 'hashed_password',
            nickname: 'TestUser'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($userId))
            ->willReturn($user);

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getPlayerById')
            ->with($brawlStarsPlayerId)
            ->willReturn($playerData);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(new PlayerId($brawlStarsPlayerId)))
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Player::class));

        // Act
        $result = $this->useCase->execute($userId, $brawlStarsPlayerId);

        // Assert
        $this->assertInstanceOf(Player::class, $result);
        $this->assertEquals($brawlStarsPlayerId, $result->getId()->getValue());
        $this->assertEquals('TestPlayer', $result->getNickname());
        $this->assertEquals(1500, $result->getTotalTrophies()->getValue());
        $this->assertEquals('GLOBAL', $result->getRegion());
    }

    public function testLinkNonExistentPlayerThrowsException(): void {
        // Arrange
        $userId = Uuid::generate();
        $brawlStarsPlayerId = 'nonexistent';
        
        $user = new User(
            id: $userId,
            email: new \App\Domain\ValueObject\Email('test@example.com'),
            passwordHash: 'hashed_password',
            nickname: 'TestUser'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($userId))
            ->willReturn($user);

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getPlayerById')
            ->with($brawlStarsPlayerId)
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->never())
            ->method('findById');

        $this->playerRepositoryMock
            ->expects($this->never())
            ->method('save');

        // Act & Assert
        $this->expectException(PlayerNotFoundException::class);
        $this->expectExceptionMessage('Player not found in Brawl Stars API');

        $this->useCase->execute($userId, $brawlStarsPlayerId);
    }

    public function testLinkPlayerAlreadyLinkedToUserThrowsException(): void {
        // Arrange
        $userId = Uuid::generate();
        $brawlStarsPlayerId = 'player456';
        
        $playerData = [
            'name' => 'TestPlayer',
            'trophies' => 1500,
            'club' => ['tag' => 'GLOBAL']
        ];

        $existingPlayer = new Player(
            new PlayerId($brawlStarsPlayerId),
            'TestPlayer',
            new Trophy(1500),
            'GLOBAL'
        );

        $user = new User(
            id: $userId,
            email: new \App\Domain\ValueObject\Email('test@example.com'),
            passwordHash: 'hashed_password',
            nickname: 'TestUser'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($userId))
            ->willReturn($user);

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getPlayerById')
            ->with($brawlStarsPlayerId)
            ->willReturn($playerData);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(new PlayerId($brawlStarsPlayerId)))
            ->willReturn($existingPlayer);

        // Act
        $result = $this->useCase->execute($userId, $brawlStarsPlayerId);

        // Assert
        $this->assertInstanceOf(Player::class, $result);
        $this->assertEquals(1500, $result->getTotalTrophies()->getValue()); // Should be updated
    }

    public function testLinkPlayerWithNullNickname(): void {
        // Arrange
        $userId = Uuid::generate();
        $brawlStarsPlayerId = 'player456';
        
        $playerData = [
            'name' => null, // API returns null
            'trophies' => 1500,
            'club' => ['tag' => 'GLOBAL']
        ];

        $player = new Player(
            new PlayerId($brawlStarsPlayerId),
            'Unknown', // Should use default
            new Trophy(1500),
            'GLOBAL'
        );

        $user = new User(
            id: $userId,
            email: new \App\Domain\ValueObject\Email('test@example.com'),
            passwordHash: 'hashed_password',
            nickname: 'TestUser'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($userId))
            ->willReturn($user);

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getPlayerById')
            ->with($brawlStarsPlayerId)
            ->willReturn($playerData);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(new PlayerId($brawlStarsPlayerId)))
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Player::class));

        // Act
        $result = $this->useCase->execute($userId, $brawlStarsPlayerId);

        // Assert
        $this->assertInstanceOf(Player::class, $result);
        $this->assertEquals('Unknown', $result->getNickname()); // Should use default
    }

    public function testLinkPlayerWithEmptyNickname(): void {
        // Arrange
        $userId = Uuid::generate();
        $brawlStarsPlayerId = 'player456';
        
        $playerData = [
            'name' => null, // API returns null
            'trophies' => 1500,
            'club' => ['tag' => 'GLOBAL']
        ];

        $player = new Player(
            new PlayerId($brawlStarsPlayerId),
            'Unknown', // Should use default
            new Trophy(1500),
            'GLOBAL'
        );

        $user = new User(
            id: $userId,
            email: new \App\Domain\ValueObject\Email('test@example.com'),
            passwordHash: 'hashed_password',
            nickname: 'TestUser'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($userId))
            ->willReturn($user);

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getPlayerById')
            ->with($brawlStarsPlayerId)
            ->willReturn($playerData);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(new PlayerId($brawlStarsPlayerId)))
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Player::class));

        // Act
        $result = $this->useCase->execute($userId, $brawlStarsPlayerId);

        // Assert
        $this->assertInstanceOf(Player::class, $result);
        $this->assertEquals('Unknown', $result->getNickname()); // Should use default
    }

    public function testLinkPlayerWithDifferentNickname(): void {
        // Arrange
        $userId = Uuid::generate();
        $brawlStarsPlayerId = 'player456';
        
        $playerData = [
            'name' => 'OriginalName',
            'trophies' => 1500,
            'club' => ['tag' => 'GLOBAL']
        ];

        $player = new Player(
            new PlayerId($brawlStarsPlayerId),
            'OriginalName',
            new Trophy(1500),
            'GLOBAL'
        );

        $user = new User(
            id: $userId,
            email: new \App\Domain\ValueObject\Email('test@example.com'),
            passwordHash: 'hashed_password',
            nickname: 'TestUser'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($userId))
            ->willReturn($user);

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getPlayerById')
            ->with($brawlStarsPlayerId)
            ->willReturn($playerData);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(new PlayerId($brawlStarsPlayerId)))
            ->willReturn($player);

        // Act
        $result = $this->useCase->execute($userId, $brawlStarsPlayerId);

        // Assert
        $this->assertInstanceOf(Player::class, $result);
        $this->assertEquals('OriginalName', $result->getNickname()); // Should use API name
    }
}
