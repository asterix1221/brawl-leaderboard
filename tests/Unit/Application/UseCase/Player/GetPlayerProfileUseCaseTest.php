<?php
namespace Tests\Unit\Application\UseCase\Player;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Player\GetPlayerProfileUseCase;
use App\Application\DTO\PlayerDTO;
use App\Domain\Entity\Player;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;
use App\Domain\Exception\PlayerNotFoundException;

class GetPlayerProfileUseCaseTest extends TestCase {
    private GetPlayerProfileUseCase $useCase;
    private $playerRepositoryMock;

    protected function setUp(): void {
        $this->playerRepositoryMock = $this->createMock(PlayerRepositoryInterface::class);
        $this->useCase = new GetPlayerProfileUseCase($this->playerRepositoryMock);
    }

    public function testGetPlayerProfileSuccessfully(): void {
        // Arrange
        $playerId = 'player123';
        $player = new Player(
            id: new PlayerId($playerId),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(1500),
            region: 'US'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(function($id) use ($playerId) {
                return $id instanceof PlayerId && $id->getValue() === $playerId;
            }))
            ->willReturn($player);

        // Act
        $result = $this->useCase->execute($playerId);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('player', $result);
        $this->assertArrayHasKey('lastSyncedAt', $result);
        
        $playerData = $result['player'];
        $this->assertEquals($playerId, $playerData['id']);
        $this->assertEquals('TestPlayer', $playerData['nickname']);
        $this->assertEquals(1500, $playerData['totalTrophies']);
        $this->assertEquals('US', $playerData['region']);
        $this->assertEquals(3, $playerData['level']); // 1500 trophies = level 3
    }

    public function testGetPlayerProfileWithNonExistentPlayerThrowsException(): void {
        // Arrange
        $playerId = 'nonexistent';

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(function($id) use ($playerId) {
                return $id instanceof PlayerId && $id->getValue() === $playerId;
            }))
            ->willReturn(null);

        // Act & Assert
        $this->expectException(PlayerNotFoundException::class);
        $this->expectExceptionMessage('Player not found');

        $this->useCase->execute($playerId);
    }

    public function testGetPlayerProfileWithLevel1(): void {
        // Arrange
        $playerId = 'player123';
        $player = new Player(
            id: new PlayerId($playerId),
            nickname: 'Beginner',
            totalTrophies: new Trophy(500),
            region: 'EU'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->willReturn($player);

        // Act
        $result = $this->useCase->execute($playerId);

        // Assert
        $this->assertIsArray($result);
        $playerData = $result['player'];
        $this->assertEquals($playerId, $playerData['id']);
        $this->assertEquals('Beginner', $playerData['nickname']);
        $this->assertEquals(500, $playerData['totalTrophies']);
        $this->assertEquals('EU', $playerData['region']);
        $this->assertEquals(2, $playerData['level']); // 500 trophies = level 2
    }

    public function testGetPlayerProfileWithLevel3(): void {
        // Arrange
        $playerId = 'player123';
        $player = new Player(
            id: new PlayerId($playerId),
            nickname: 'Advanced',
            totalTrophies: new Trophy(2000),
            region: 'ASIA'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->willReturn($player);

        // Act
        $result = $this->useCase->execute($playerId);

        // Assert
        $this->assertIsArray($result);
        $playerData = $result['player'];
        $this->assertEquals($playerId, $playerData['id']);
        $this->assertEquals('Advanced', $playerData['nickname']);
        $this->assertEquals(2000, $playerData['totalTrophies']);
        $this->assertEquals('ASIA', $playerData['region']);
        $this->assertEquals(3, $playerData['level']); // 2000 trophies = level 3
    }

    public function testGetPlayerProfileWithLevel4(): void {
        // Arrange
        $playerId = 'player123';
        $player = new Player(
            id: new PlayerId($playerId),
            nickname: 'Pro',
            totalTrophies: new Trophy(3500),
            region: 'RU'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->willReturn($player);

        // Act
        $result = $this->useCase->execute($playerId);

        // Assert
        $this->assertIsArray($result);
        $playerData = $result['player'];
        $this->assertEquals($playerId, $playerData['id']);
        $this->assertEquals('Pro', $playerData['nickname']);
        $this->assertEquals(3500, $playerData['totalTrophies']);
        $this->assertEquals('RU', $playerData['region']);
        $this->assertEquals(4, $playerData['level']); // 3500 trophies = level 4
    }

    public function testGetPlayerProfileWithZeroTrophies(): void {
        // Arrange
        $playerId = 'player123';
        $player = new Player(
            id: new PlayerId($playerId),
            nickname: 'Newbie',
            totalTrophies: new Trophy(0),
            region: 'US'
        );

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->willReturn($player);

        // Act
        $result = $this->useCase->execute($playerId);

        // Assert
        $this->assertIsArray($result);
        $playerData = $result['player'];
        $this->assertEquals($playerId, $playerData['id']);
        $this->assertEquals('Newbie', $playerData['nickname']);
        $this->assertEquals(0, $playerData['totalTrophies']);
        $this->assertEquals('US', $playerData['region']);
        $this->assertEquals(1, $playerData['level']); // 0 trophies = level 1
    }

    public function testGetPlayerProfileWithEmptyPlayerIdThrowsException(): void {
        // Arrange
        $playerId = '';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Player ID cannot be empty');

        // Act
        $this->useCase->execute($playerId);
    }
}
