<?php
namespace Tests\Unit\Application\UseCase\Player;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Player\LinkBrawlStarsPlayerUseCase;
use App\Application\DTO\LinkPlayerRequestDTO;
use App\Application\DTO\PlayerResponseDTO;
use App\Domain\Entity\Player;
use App\Domain\Entity\User;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;
use App\Domain\Exception\PlayerAlreadyLinkedToUserException;
use App\Domain\Exception\PlayerNotFoundException;

class LinkBrawlStarsPlayerUseCaseTest extends TestCase {
    private LinkBrawlStarsPlayerUseCase $useCase;
    private $playerRepositoryMock;
    private $userRepositoryMock;

    protected function setUp(): void {
        $this->playerRepositoryMock = $this->createMock(PlayerRepositoryInterface::class);
        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);

        $this->useCase = new LinkBrawlStarsPlayerUseCase(
            $this->playerRepositoryMock,
            $this->userRepositoryMock
        );
    }

    public function testLinkPlayerSuccessfully(): void {
        // Arrange
        $request = new LinkPlayerRequestDTO(
            userId: 'user123',
            brawlStarsId: 'player456',
            nickname: 'TestPlayer',
            region: 'US'
        );

        $player = new Player(
            id: new PlayerId('player456'),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(1500),
            region: 'US'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findByBrawlStarsId')
            ->with('player456')
            ->willReturn($player);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findLinkedPlayerId')
            ->with('user123')
            ->willReturn(null);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('linkPlayerToUser')
            ->with('user123', 'player456');

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(PlayerResponseDTO::class, $result);
        $this->assertEquals('player456', $result->id);
        $this->assertEquals('TestPlayer', $result->nickname);
        $this->assertEquals(1500, $result->totalTrophies);
        $this->assertEquals('US', $result->region);
    }

    public function testLinkNonExistentPlayerThrowsException(): void {
        // Arrange
        $request = new LinkPlayerRequestDTO(
            userId: 'user123',
            brawlStarsId: 'nonexistent',
            nickname: 'TestPlayer',
            region: 'US'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findByBrawlStarsId')
            ->with('nonexistent')
            ->willReturn(null);

        $this->userRepositoryMock
            ->expects($this->never())
            ->method('findLinkedPlayerId');

        $this->userRepositoryMock
            ->expects($this->never())
            ->method('linkPlayerToUser');

        // Act & Assert
        $this->expectException(PlayerNotFoundException::class);
        $this->expectExceptionMessage('Player not found');

        $this->useCase->execute($request);
    }

    public function testLinkPlayerAlreadyLinkedToUserThrowsException(): void {
        // Arrange
        $request = new LinkPlayerRequestDTO(
            userId: 'user123',
            brawlStarsId: 'player456',
            nickname: 'TestPlayer',
            region: 'US'
        );

        $player = new Player(
            id: new PlayerId('player456'),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(1500),
            region: 'US'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findByBrawlStarsId')
            ->with('player456')
            ->willReturn($player);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findLinkedPlayerId')
            ->with('user123')
            ->willReturn('already_linked_player');

        $this->userRepositoryMock
            ->expects($this->never())
            ->method('linkPlayerToUser');

        // Act & Assert
        $this->expectException(PlayerAlreadyLinkedToUserException::class);
        $this->expectExceptionMessage('User already has a linked player');

        $this->useCase->execute($request);
    }

    public function testLinkPlayerWithNullNickname(): void {
        // Arrange
        $request = new LinkPlayerRequestDTO(
            userId: 'user123',
            brawlStarsId: 'player456',
            nickname: null,
            region: 'US'
        );

        $player = new Player(
            id: new PlayerId('player456'),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(1500),
            region: 'US'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findByBrawlStarsId')
            ->willReturn($player);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findLinkedPlayerId')
            ->willReturn(null);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('linkPlayerToUser');

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(PlayerResponseDTO::class, $result);
        $this->assertEquals('player456', $result->id);
        $this->assertEquals('TestPlayer', $result->nickname); // Should use player's nickname
    }

    public function testLinkPlayerWithEmptyNickname(): void {
        // Arrange
        $request = new LinkPlayerRequestDTO(
            userId: 'user123',
            brawlStarsId: 'player456',
            nickname: '',
            region: 'US'
        );

        $player = new Player(
            id: new PlayerId('player456'),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(1500),
            region: 'US'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findByBrawlStarsId')
            ->willReturn($player);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findLinkedPlayerId')
            ->willReturn(null);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('linkPlayerToUser');

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(PlayerResponseDTO::class, $result);
        $this->assertEquals('player456', $result->id);
        $this->assertEquals('TestPlayer', $result->nickname); // Should use player's nickname
    }

    public function testLinkPlayerWithDifferentNickname(): void {
        // Arrange
        $request = new LinkPlayerRequestDTO(
            userId: 'user123',
            brawlStarsId: 'player456',
            nickname: 'NewNickname',
            region: 'US'
        );

        $player = new Player(
            id: new PlayerId('player456'),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(1500),
            region: 'US'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findByBrawlStarsId')
            ->willReturn($player);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findLinkedPlayerId')
            ->willReturn(null);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('linkPlayerToUser');

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(PlayerResponseDTO::class, $result);
        $this->assertEquals('player456', $result->id);
        $this->assertEquals('NewNickname', $result->nickname); // Should use provided nickname
    }
}
